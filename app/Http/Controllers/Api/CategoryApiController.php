<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryApiController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::orderBy('name')->get();

        return response()->json(['data' => $categories]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create-categories');

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'slug'        => 'required|string|max:120|unique:categories,slug',
            'description' => 'nullable|string',
            'color'       => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon'        => 'nullable|string|max:50',
            'status'      => 'boolean',
        ]);

        $category = Category::create($validated);

        return response()->json([
            'message' => 'Categoría creada.',
            'data'    => $category,
        ], 201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $this->authorize('edit-categories');

        $validated = $request->validate([
            'name'        => 'sometimes|string|max:100',
            'slug'        => 'sometimes|string|max:120|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'color'       => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon'        => 'nullable|string|max:50',
            'status'      => 'boolean',
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'Categoría actualizada.',
            'data'    => $category,
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete-categories');

        $category->delete();

        return response()->json(['message' => 'Categoría eliminada.']);
    }
}
