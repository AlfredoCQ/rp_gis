<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class MapFeature extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'layer_id', 'type', 'name', 'description',
        'geometry', 'properties',
        'color', 'icon', 'opacity',
        'status', 'category_id',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'properties' => 'array',
        'opacity'    => 'float',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function layer(): BelongsTo
    {
        return $this->belongsTo(Layer::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByLayer($query, int $layerId)
    {
        return $query->where('layer_id', $layerId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Filtrar features dentro de un bounding box.
     * @param array $bbox [minLng, minLat, maxLng, maxLat]
     */
    public function scopeWithinBbox($query, array $bbox)
    {
        [$minLng, $minLat, $maxLng, $maxLat] = $bbox;
        $envelope = "ST_MakeEnvelope({$minLng}, {$minLat}, {$maxLng}, {$maxLat}, 4326)";

        return $query->whereNotNull('geometry')
            ->whereRaw("ST_Intersects(geometry, {$envelope})");
    }

    /**
     * Filtrar features dentro de un radio en metros.
     */
    public function scopeNearby($query, float $lat, float $lng, int $radiusMeters)
    {
        return $query->whereNotNull('geometry')
            ->whereRaw(
                "ST_DWithin(geometry::geography, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, ?)",
                [$lng, $lat, $radiusMeters]
            );
    }

    /**
     * Obtener geometría como GeoJSON.
     */
    public function getGeoJsonGeometryAttribute(): ?array
    {
        if (!$this->geometry) {
            return null;
        }

        $result = DB::selectOne(
            'SELECT ST_AsGeoJSON(geometry) as geojson FROM map_features WHERE id = ?',
            [$this->id]
        );

        return $result ? json_decode($result->geojson, true) : null;
    }

    /**
     * Calcular área en m² (solo para polígonos).
     */
    public function getAreaM2Attribute(): ?float
    {
        if ($this->type !== 'polygon') {
            return null;
        }

        $result = DB::selectOne(
            'SELECT ST_Area(geometry::geography) as area FROM map_features WHERE id = ?',
            [$this->id]
        );

        return $result?->area;
    }

    /**
     * Retornar como Feature GeoJSON completo.
     */
    public function toGeoJsonFeature(): array
    {
        return [
            'type'       => 'Feature',
            'id'         => $this->id,
            'geometry'   => $this->geo_json_geometry,
            'properties' => array_merge($this->properties ?? [], [
                'id'          => $this->id,
                'layer_id'    => $this->layer_id,
                'type'        => $this->type,
                'name'        => $this->name,
                'description' => $this->description,
                'color'       => $this->color,
                'icon'        => $this->icon,
                'status'      => $this->status,
                'category_id' => $this->category_id,
                'created_at'  => $this->created_at?->toIso8601String(),
            ]),
        ];
    }
}
