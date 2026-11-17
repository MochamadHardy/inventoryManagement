<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(): JsonResponse
    {
        $categories = Category::withCount('products')->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories,
        ]);
    }

    /**
     * Store a newly created category.
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);

        // Pastikan slug unik
        $originalSlug = $data['slug'];
        $count = 1;
        while (Category::where('slug', $data['slug'])->exists()) {
            $data['slug'] = "{$originalSlug}-{$count}";
            $count++;
        }

        $category = Category::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Kategori berhasil ditambahkan.',
            'data' => $category,
        ], 201);
    }

    /**
     * Update the specified category.
     */
    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);

        // Pastikan slug unik (abaikan kategori saat ini)
        $originalSlug = $data['slug'];
        $count = 1;
        while (Category::where('slug', $data['slug'])->where('id', '!=', $category->id)->exists()) {
            $data['slug'] = "{$originalSlug}-{$count}";
            $count++;
        }

        $category->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Kategori berhasil diperbarui.',
            'data' => $category,
        ]);
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category): JsonResponse
    {
        if ($category->products()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kategori tidak dapat dihapus karena masih digunakan oleh produk aktif.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }
}
