<?php

use App\Http\Controllers\Api\HeatmapApiController;
use App\Http\Controllers\Api\LayerApiController;
use App\Http\Controllers\Api\MapFeatureApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Sistema GIS
|--------------------------------------------------------------------------
| Todas las rutas requieren autenticación con Sanctum.
| Las acciones de escritura validan permisos via Policy/authorize().
*/

Route::middleware('auth:sanctum')->group(function () {

    // ─── Capas ────────────────────────────────────────────────────────────────
    Route::apiResource('layers', LayerApiController::class);
    Route::patch('layers/{layer}/toggle', [LayerApiController::class, 'toggle'])
        ->name('layers.toggle');
    Route::post('layers/reorder', [LayerApiController::class, 'reorder'])
        ->name('layers.reorder');

    // ─── Features geográficos ─────────────────────────────────────────────────
    Route::get('features/nearby', [MapFeatureApiController::class, 'nearby'])
        ->name('features.nearby');
    Route::get('features/geojson/{layerId}', [MapFeatureApiController::class, 'exportGeoJson'])
        ->name('features.export.geojson');
    Route::post('features/import/{layerId}', [MapFeatureApiController::class, 'importGeoJson'])
        ->name('features.import.geojson');

    Route::apiResource('features', MapFeatureApiController::class);

    // ─── Mapa de Calor ────────────────────────────────────────────────────────
    Route::get('heatmap/{layerId}', [HeatmapApiController::class, 'points'])
        ->name('heatmap.points');

    // ─── Categorías ───────────────────────────────────────────────────────────
    Route::apiResource('categories', \App\Http\Controllers\Api\CategoryApiController::class)
        ->only(['index', 'store', 'update', 'destroy']);

});
