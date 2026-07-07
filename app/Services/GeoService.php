<?php

namespace App\Services;

use App\Models\MapFeature;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class GeoService
{
    /**
     * Insertar o actualizar la geometría de un feature desde GeoJSON.
     *
     * @param int    $featureId
     * @param array  $geoJsonGeometry  ej: {"type":"Point","coordinates":[-74.0,4.6]}
     */
    public function setGeometry(int $featureId, array $geoJsonGeometry): void
    {
        $geoJsonStr = json_encode($geoJsonGeometry);

        DB::statement(
            'UPDATE map_features SET geometry = ST_SetSRID(ST_GeomFromGeoJSON(?), 4326) WHERE id = ?',
            [$geoJsonStr, $featureId]
        );
    }

    /**
     * Insertar geometría desde WKT.
     */
    public function setGeometryFromWkt(int $featureId, string $wkt): void
    {
        DB::statement(
            'UPDATE map_features SET geometry = ST_GeomFromText(?, 4326) WHERE id = ?',
            [$wkt, $featureId]
        );
    }

    /**
     * Obtener features dentro de un bounding box como GeoJSON FeatureCollection.
     *
     * @param array $bbox       [minLng, minLat, maxLng, maxLat]
     * @param array $filters    ['layer_id', 'type', 'status', 'category_id']
     */
    public function featuresInBbox(array $bbox, array $filters = []): array
    {
        [$minLng, $minLat, $maxLng, $maxLat] = $bbox;

        $query = MapFeature::query()
            ->select([
                'id', 'layer_id', 'type', 'name', 'description',
                'color', 'icon', 'status', 'category_id',
                'properties', 'created_by', 'created_at',
                DB::raw('ST_AsGeoJSON(geometry) as geojson'),
            ])
            ->whereNotNull('geometry')
            ->whereRaw(
                "ST_Intersects(geometry, ST_MakeEnvelope(?, ?, ?, ?, 4326))",
                [$minLng, $minLat, $maxLng, $maxLat]
            )
            ->where('status', 'active');

        // Aplicar filtros opcionales
        if (!empty($filters['layer_id'])) {
            $query->where('layer_id', $filters['layer_id']);
        }
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $features = $query->limit(5000)->get(); // límite de seguridad

        return $this->toFeatureCollection($features);
    }

    /**
     * Obtener features cercanas a una coordenada dentro de un radio.
     *
     * @param float $lat
     * @param float $lng
     * @param int   $radiusMeters
     * @param array $filters
     */
    public function featuresNearby(float $lat, float $lng, int $radiusMeters, array $filters = []): array
    {
        $query = MapFeature::query()
            ->select([
                'id', 'layer_id', 'type', 'name', 'description',
                'color', 'icon', 'status', 'category_id', 'properties',
                DB::raw('ST_AsGeoJSON(geometry) as geojson'),
                DB::raw("ST_Distance(geometry::geography, ST_SetSRID(ST_MakePoint($lng, $lat), 4326)::geography) as distance_m"),
            ])
            ->whereNotNull('geometry')
            ->whereRaw(
                "ST_DWithin(geometry::geography, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, ?)",
                [$lng, $lat, $radiusMeters]
            )
            ->orderBy('distance_m');

        if (!empty($filters['layer_id'])) {
            $query->where('layer_id', $filters['layer_id']);
        }

        $features = $query->limit(1000)->get();

        return $this->toFeatureCollection($features);
    }

    /**
     * Obtener puntos para mapa de calor (lat/lng + intensidad opcional).
     *
     * @param int   $layerId
     * @param array $filters  ['date_from', 'date_to', 'category_id', 'status']
     */
    public function heatmapPoints(int $layerId, array $filters = []): array
    {
        $query = MapFeature::query()
            ->select([
                DB::raw('ST_Y(geometry::geometry) as lat'),
                DB::raw('ST_X(geometry::geometry) as lng'),
                DB::raw("COALESCE((properties->>'intensity')::float, 1.0) as intensity"),
            ])
            ->where('layer_id', $layerId)
            ->where('type', 'heat_point')
            ->whereNotNull('geometry')
            ->where('status', 'active');

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query->get()->map(fn($r) => [
            (float) $r->lat,
            (float) $r->lng,
            (float) $r->intensity,
        ])->all();
    }

    /**
     * Exportar una capa completa como GeoJSON FeatureCollection.
     */
    public function exportLayerAsGeoJson(int $layerId): array
    {
        $features = MapFeature::query()
            ->select([
                'id', 'layer_id', 'type', 'name', 'description',
                'color', 'icon', 'status', 'category_id', 'properties',
                'created_at', 'updated_at',
                DB::raw('ST_AsGeoJSON(geometry) as geojson'),
            ])
            ->where('layer_id', $layerId)
            ->whereNotNull('geometry')
            ->whereNull('deleted_at')
            ->get();

        return $this->toFeatureCollection($features);
    }

    /**
     * Importar un FeatureCollection GeoJSON a una capa.
     *
     * @param int   $layerId
     * @param array $geoJsonCollection
     * @param int   $userId
     * @return int  número de features importados
     */
    public function importGeoJsonCollection(int $layerId, array $geoJsonCollection, int $userId): int
    {
        $count = 0;

        foreach ($geoJsonCollection['features'] ?? [] as $feature) {
            $props = $feature['properties'] ?? [];
            $geom  = $feature['geometry'] ?? null;

            if (!$geom) {
                continue;
            }

            // Determinar tipo de feature según geometría
            $type = match (strtolower($geom['type'] ?? '')) {
                'point'           => 'marker',
                'multipoint'      => 'heat_point',
                'linestring',
                'multilinestring' => 'line',
                'polygon',
                'multipolygon'    => 'polygon',
                default           => 'marker',
            };

            $mapFeature = MapFeature::create([
                'layer_id'   => $layerId,
                'type'       => $type,
                'name'       => $props['name'] ?? "Importado #{$count}",
                'description'=> $props['description'] ?? null,
                'properties' => $props,
                'color'      => $props['color'] ?? '#3B82F6',
                'status'     => 'active',
                'created_by' => $userId,
            ]);

            $this->setGeometry($mapFeature->id, $geom);
            $count++;
        }

        return $count;
    }

    // ─── Helpers privados ─────────────────────────────────────────────────────

    /**
     * Convertir colección de DB rows a GeoJSON FeatureCollection.
     */
    private function toFeatureCollection(Collection $features): array
    {
        return [
            'type'     => 'FeatureCollection',
            'features' => $features->map(function ($f) {
                $geometry = $f->geojson ? json_decode($f->geojson, true) : null;
                $props = is_array($f->properties) ? $f->properties : (json_decode($f->properties ?? '{}', true) ?? []);

                return [
                    'type'       => 'Feature',
                    'id'         => $f->id,
                    'geometry'   => $geometry,
                    'properties' => array_merge($props, [
                        'id'          => $f->id,
                        'layer_id'    => $f->layer_id,
                        'type'        => $f->type,
                        'name'        => $f->name,
                        'description' => $f->description,
                        'color'       => $f->color,
                        'icon'        => $f->icon,
                        'status'      => $f->status,
                        'category_id' => $f->category_id,
                        'distance_m'  => $f->distance_m ?? null,
                    ]),
                ];
            })->values()->all(),
        ];
    }
}
