<?php

use App\Http\Controllers\Api\HeatmapApiController;
use App\Http\Controllers\Api\LayerApiController;
use App\Http\Controllers\Api\MapFeatureApiController;
use App\Http\Controllers\Api\CategoryApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Sistema GIS
|--------------------------------------------------------------------------
| Rutas públicas accesibles por cualquier visitante, y rutas protegidas
| para usuarios autenticados que gestionan las capas y datos.
*/

// ─── Rutas Públicas (Lectura para visitantes sin iniciar sesión) ─────────────
Route::get('layers', [LayerApiController::class, 'index'])->name('layers.index');
Route::get('layers/{layer}', [LayerApiController::class, 'show'])->name('layers.show');

Route::get('features', [MapFeatureApiController::class, 'index'])->name('features.index');
Route::get('features/nearby', [MapFeatureApiController::class, 'nearby'])->name('features.nearby');
Route::get('features/{feature}', [MapFeatureApiController::class, 'show'])->name('features.show');

Route::get('heatmap/{layerId}', [HeatmapApiController::class, 'points'])->name('heatmap.points');

Route::get('categories', [CategoryApiController::class, 'index'])->name('categories.index');


// ─── Rutas Protegidas (Escritura y Gestión Administrativa con Sanctum) ───────
Route::middleware('auth:sanctum')->group(function () {

    // Capas
    Route::post('layers', [LayerApiController::class, 'store'])->name('layers.store');
    Route::put('layers/{layer}', [LayerApiController::class, 'update'])->name('layers.update');
    Route::delete('layers/{layer}', [LayerApiController::class, 'destroy'])->name('layers.destroy');
    Route::patch('layers/{layer}/toggle', [LayerApiController::class, 'toggle'])
        ->name('layers.toggle');
    Route::post('layers/reorder', [LayerApiController::class, 'reorder'])
        ->name('layers.reorder');

    // Features geográficos
    Route::post('features', [MapFeatureApiController::class, 'store'])->name('features.store');
    Route::put('features/{feature}', [MapFeatureApiController::class, 'update'])->name('features.update');
    Route::delete('features/{feature}', [MapFeatureApiController::class, 'destroy'])->name('features.destroy');
    Route::get('features/geojson/{layerId}', [MapFeatureApiController::class, 'exportGeoJson'])
        ->name('features.export.geojson');
    Route::post('features/import/{layerId}', [MapFeatureApiController::class, 'importGeoJson'])
        ->name('features.import.geojson');

    // Categorías
    Route::post('categories', [CategoryApiController::class, 'store'])->name('categories.store');
    Route::put('categories/{category}', [CategoryApiController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryApiController::class, 'destroy'])->name('categories.destroy');

});
