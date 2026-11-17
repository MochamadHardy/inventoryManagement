<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\UnitRequest;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;

class UnitController extends Controller
{
    /**
     * Display a listing of units.
     */
    public function index(): JsonResponse
    {
        $units = Unit::withCount('productUnits')->orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $units,
        ]);
    }

    /**
     * Store a newly created unit.
     */
    public function store(UnitRequest $request): JsonResponse
    {
        $unit = Unit::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Satuan unit berhasil ditambahkan.',
            'data' => $unit,
        ], 201);
    }

    /**
     * Update the specified unit.
     */
    public function update(UnitRequest $request, Unit $unit): JsonResponse
    {
        $unit->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Satuan unit berhasil diperbarui.',
            'data' => $unit,
        ]);
    }

    /**
     * Remove the specified unit.
     */
    public function destroy(Unit $unit): JsonResponse
    {
        if ($unit->productUnits()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Satuan tidak dapat dihapus karena masih dikaitkan dengan produk.',
            ], 422);
        }

        $unit->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Satuan unit berhasil dihapus.',
        ]);
    }
}
