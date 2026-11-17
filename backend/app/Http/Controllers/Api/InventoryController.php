<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BranchStock;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display a listing of branch stocks with filters.
     */
    public function branchStocks(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = BranchStock::with([
            'branch',
            'variant.product.category',
            'variant.prices.unit',
        ]);

        // Pembatasan cabang: Kasir hanya bisa melihat stok cabangnya sendiri
        if ($user->role === 'branch_cashier') {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('variant', function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('variant_name', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($p) use ($search) {
                      $p->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter stok di bawah batas aman (Safety Stock)
        if ($request->boolean('low_stock_only')) {
            $query->whereColumn('quantity', '<=', 'min_stock_alert');
        }

        $stocks = $query->orderBy('branch_id')->get();

        return response()->json([
            'status' => 'success',
            'data' => $stocks,
        ]);
    }

    /**
     * Display chronological stock movements (Audit Trail / Kartu Stok).
     */
    public function stockMovements(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = StockMovement::with([
            'branch',
            'variant.product',
            'user',
        ])->latest('id');

        // Pembatasan akses cabang jika bukan super admin atau gudang pusat
        if ($user->role === 'branch_cashier') {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter tipe pergerakan stok
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter varian spesifik
        if ($request->filled('product_variant_id')) {
            $query->where('product_variant_id', $request->product_variant_id);
        }

        // Batasi 200 record terbaru untuk performa
        $movements = $query->limit(200)->get();

        return response()->json([
            'status' => 'success',
            'data' => $movements,
        ]);
    }
}
