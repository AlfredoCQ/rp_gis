<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MapFeature;
use App\Services\GeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MapFeatureApiController extends Controller
{
    public function __construct(private readonly GeoService $geo) {}

    /**
     * GET /api/features
     * Listar features con filtros. Si se pasan bbox, carga por área.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bbox'        => 'nullable|string',   // "minLng,minLat,maxLng,maxLat"
            'layer_id'    => 'nullable|integer',
            'type'        => 'nullable|in:marker,polygon,line,heat_point',
            'status'      => 'nullable|in:active,inactive,draft',
            'category_id' => 'nullable|integer',
            'search'      => 'nullable|string|max:255',
            'per_page'    => 'nullable|integer|max:500',
        ]);

        // Carga por bounding box (modo mapa interactivo)
        if (!empty($validated['bbox'])) {
            $bbox = array_map('floatval', explode(',', $validated['bbox']));
            if (count($bbox) !== 4) {
                return response()->json(['error' => 'bbox inválido. Formato: minLng,minLat,maxLng,maxLat'], 422);
            }

            $collection = $this->geo->featuresInBbox($bbox, $validated);
            return response()->json($collection);
        }

        // Modo listado paginado
        $query = MapFeature::query()
            ->select([
                'id', 'layer_id', 'type', 'name', 'description',
                'color', 'icon', 'status', 'category_id', 'properties',
                'created_by', 'created_at',
                DB::raw('ST_AsGeoJSON(geometry) as geojson'),
            ])
            ->whereNull('deleted_at');

        if (!empty($validated['layer_id']))    $query->where('layer_id', $validated['layer_id']);
        if (!empty($validated['type']))        $query->where('type', $validated['type']);
        if (!empty($validated['status']))      $query->where('status', $validated['status']);
        if (!empty($validated['category_id'])) $query->where('category_id', $validated['category_id']);
        if (!empty($validated['search'])) {
            $q = '%' . $validated['search'] . '%';
            $query->where(fn($q2) => $q2
                ->where('name', 'ilike', $q)
                ->orWhere('description', 'ilike', $q)
            );
        }

        $perPage = $validated['per_page'] ?? 50;
        $results = $query->latest()->paginate($perPage);

        // Transformar a GeoJSON inline
        $results->getCollection()->transform(function ($f) {
            $f->geometry = $f->geojson ? json_decode($f->geojson, true) : null;
            unset($f->geojson);
            return $f;
        });

        return response()->json($results);
    }

    /**
     * POST /api/features
     * Crear nuevo feature geográfico.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create-features');

        $validated = $request->validate([
            'layer_id'    => 'required|exists:layers,id',
            'type'        => 'required|in:marker,polygon,line,heat_point',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'geometry'    => 'required|array',       // GeoJSON geometry object
            'geometry.type'        => 'required|string',
            'geometry.coordinates' => 'required',
            'properties'  => 'nullable|array',
            'color'       => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon'        => 'nullable|string|max:50',
            'opacity'     => 'nullable|numeric|between:0,1',
            'status'      => 'nullable|in:active,inactive,draft',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $feature = MapFeature::create(array_merge($validated, [
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]));

        // Guardar geometría PostGIS
        $this->geo->setGeometry($feature->id, $validated['geometry']);

        return response()->json([
            'message' => 'Feature creado correctamente.',
            'data'    => $feature->fresh(),
        ], 201);
    }

    /**
     * GET /api/features/{id}
     * Ver un feature con su geometría GeoJSON.
     */
    public function show(int $id): JsonResponse
    {
        $feature = MapFeature::query()
            ->select([
                'map_features.*',
                DB::raw('ST_AsGeoJSON(geometry) as geojson'),
                DB::raw('ST_Area(geometry::geography) as area_m2'),
            ])
            ->findOrFail($id);

        $data = $feature->toArray();
        $data['geometry'] = $feature->geojson ? json_decode($feature->geojson, true) : null;
        $data['area_m2']  = $feature->area_m2;
        unset($data['geojson']);

        return response()->json(['data' => $data]);
    }

    /**
     * PUT /api/features/{id}
     * Actualizar feature (atributos y/o geometría).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $feature = MapFeature::findOrFail($id);
        $this->authorize('edit-features');

        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'geometry'    => 'sometimes|array',
            'properties'  => 'nullable|array',
            'color'       => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon'        => 'nullable|string|max:50',
            'opacity'     => 'nullable|numeric|between:0,1',
            'status'      => 'nullable|in:active,inactive,draft',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $feature->update(array_merge(
            $validated,
            ['updated_by' => Auth::id()]
        ));

        if (!empty($validated['geometry'])) {
            $this->geo->setGeometry($feature->id, $validated['geometry']);
        }

        return response()->json([
            'message' => 'Feature actualizado.',
            'data'    => $feature->fresh(),
        ]);
    }

    /**
     * DELETE /api/features/{id}
     * Soft delete de un feature.
     */
    public function destroy(int $id): JsonResponse
    {
        $feature = MapFeature::findOrFail($id);
        $this->authorize('delete-features');

        $feature->delete();

        return response()->json(['message' => 'Feature eliminado.']);
    }

    /**
     * GET /api/features/nearby
     * Features cercanos a una coordenada.
     */
    public function nearby(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat'      => 'required|numeric|between:-90,90',
            'lng'      => 'required|numeric|between:-180,180',
            'radius'   => 'required|integer|min:1|max:100000', // metros
            'layer_id' => 'nullable|integer',
        ]);

        $collection = $this->geo->featuresNearby(
            $validated['lat'],
            $validated['lng'],
            $validated['radius'],
            ['layer_id' => $validated['layer_id'] ?? null]
        );

        return response()->json($collection);
    }

    /**
     * GET /api/features/geojson/{layerId}
     * Exportar capa completa como GeoJSON.
     */
    public function exportGeoJson(int $layerId): JsonResponse
    {
        $collection = $this->geo->exportLayerAsGeoJson($layerId);

        return response()->json($collection)
            ->header('Content-Disposition', "attachment; filename=\"layer-{$layerId}.geojson\"")
            ->header('Content-Type', 'application/geo+json');
    }

    /**
     * POST /api/features/import/{layerId}
     * Importar GeoJSON a una capa.
     */
    public function importGeoJson(Request $request, int $layerId): JsonResponse
    {
        $this->authorize('import-data');

        $request->validate([
            'geojson'      => 'required|array',
            'geojson.type' => 'required|in:FeatureCollection',
        ]);

        $count = $this->geo->importGeoJsonCollection(
            $layerId,
            $request->input('geojson'),
            Auth::id()
        );

        return response()->json([
            'message' => "{$count} features importados correctamente.",
            'count'   => $count,
        ]);
    }
}
