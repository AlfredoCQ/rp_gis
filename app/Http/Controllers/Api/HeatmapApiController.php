<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HeatmapApiController extends Controller
{
    public function __construct(private readonly GeoService $geo) {}

    /**
     * GET /api/heatmap/{layerId}
     * Retorna array de [lat, lng, intensity] para Leaflet.heat
     */
    public function points(Request $request, int $layerId): JsonResponse
    {
        $filters = $request->validate([
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
            'category_id' => 'nullable|integer',
            'status'      => 'nullable|in:active,inactive',
        ]);

        $points = $this->geo->heatmapPoints($layerId, $filters);

        return response()->json([
            'layer_id' => $layerId,
            'count'    => count($points),
            'points'   => $points,   // [[lat, lng, intensity], ...]
        ]);
    }
}
