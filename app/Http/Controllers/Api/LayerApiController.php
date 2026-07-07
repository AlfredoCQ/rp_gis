<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Layer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LayerApiController extends Controller
{
    /**
     * GET /api/layers
     */
    public function index(): JsonResponse
    {
        $layers = Layer::query()
            ->with(['styles', 'fields'])
            ->ordered()
            ->get();

        return response()->json(['data' => $layers]);
    }

    /**
     * POST /api/layers
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create-layers');

        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'slug'        => 'required|string|max:170|unique:layers,slug',
            'description' => 'nullable|string',
            'type'        => 'required|in:marker,polygon,line,heatmap',
            'color'       => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon'        => 'nullable|string|max:50',
            'opacity'     => 'nullable|numeric|between:0,1',
            'min_zoom'    => 'nullable|integer|min:1|max:22',
            'max_zoom'    => 'nullable|integer|min:1|max:22',
            'is_active'   => 'nullable|boolean',
            'is_public'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer',
        ]);

        $layer = Layer::create(array_merge($validated, [
            'created_by' => Auth::id(),
        ]));

        return response()->json([
            'message' => 'Capa creada correctamente.',
            'data'    => $layer,
        ], 201);
    }

    /**
     * GET /api/layers/{id}
     */
    public function show(int $id): JsonResponse
    {
        $layer = Layer::with(['styles', 'fields'])->findOrFail($id);

        return response()->json(['data' => $layer]);
    }

    /**
     * PUT /api/layers/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorize('edit-layers');

        $layer = Layer::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'sometimes|string|max:150',
            'slug'        => 'sometimes|string|max:170|unique:layers,slug,' . $layer->id,
            'description' => 'nullable|string',
            'type'        => 'sometimes|in:marker,polygon,line,heatmap',
            'color'       => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon'        => 'nullable|string|max:50',
            'opacity'     => 'nullable|numeric|between:0,1',
            'min_zoom'    => 'nullable|integer|min:1|max:22',
            'max_zoom'    => 'nullable|integer|min:1|max:22',
            'is_active'   => 'nullable|boolean',
            'is_public'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer',
        ]);

        $layer->update($validated);

        return response()->json([
            'message' => 'Capa actualizada correctamente.',
            'data'    => $layer->fresh(),
        ]);
    }

    /**
     * DELETE /api/layers/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete-layers');

        $layer = Layer::findOrFail($id);
        $layer->delete();

        return response()->json(['message' => 'Capa eliminada.']);
    }

    /**
     * PATCH /api/layers/{id}/toggle
     */
    public function toggle(int $id): JsonResponse
    {
        $this->authorize('edit-layers');

        $layer = Layer::findOrFail($id);
        $layer->update(['is_active' => !$layer->is_active]);

        return response()->json([
            'message' => 'Estado de la capa actualizado.',
            'is_active' => $layer->is_active,
        ]);
    }

    /**
     * POST /api/layers/reorder
     * Espera un array: ['layers' => [['id' => 1, 'sort_order' => 0], ...]]
     */
    public function reorder(Request $request): JsonResponse
    {
        $this->authorize('reorder-layers');

        $validated = $request->validate([
            'layers'              => 'required|array',
            'layers.*.id'         => 'required|exists:layers,id',
            'layers.*.sort_order' => 'required|integer',
        ]);

        foreach ($validated['layers'] as $item) {
            Layer::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['message' => 'Orden de capas actualizado.']);
    }
}
