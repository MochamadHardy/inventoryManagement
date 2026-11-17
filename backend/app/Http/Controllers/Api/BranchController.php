<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\BranchRequest;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;

class BranchController extends Controller
{
    /**
     * Display a listing of branches.
     */
    public function index(): JsonResponse
    {
        $branches = Branch::withCount(['users', 'stocks'])->orderBy('is_central_warehouse', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $branches,
        ]);
    }

    /**
     * Store a newly created branch.
     */
    public function store(BranchRequest $request): JsonResponse
    {
        $branch = Branch::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Cabang berhasil ditambahkan.',
            'data' => $branch,
        ], 201);
    }

    /**
     * Display the specified branch.
     */
    public function show(Branch $branch): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $branch->load(['users', 'stocks.variant.product']),
        ]);
    }

    /**
     * Update the specified branch.
     */
    public function update(BranchRequest $request, Branch $branch): JsonResponse
    {
        $branch->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Data cabang berhasil diperbarui.',
            'data' => $branch,
        ]);
    }

    /**
     * Remove the specified branch.
     */
    public function destroy(Branch $branch): JsonResponse
    {
        // Proteksi: jangan hapus cabang jika memiliki riwayat transaksi atau stok
        if ($branch->stocks()->where('quantity', '>', 0)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cabang tidak dapat dihapus karena masih memiliki saldo stok barang.',
            ], 422);
        }

        if ($branch->sales()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cabang tidak dapat dihapus karena memiliki riwayat transaksi penjualan.',
            ], 422);
        }

        $branch->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Cabang berhasil dihapus.',
        ]);
    }
}
