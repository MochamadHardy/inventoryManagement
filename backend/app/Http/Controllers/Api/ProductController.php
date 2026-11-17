<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\ProductRequest;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\ProductUnit;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of products with variants and stocks.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with([
            'category',
            'productUnits.unit',
            'variants.prices.unit',
            'variants.branchStocks.branch',
        ]);

        // Filter by Category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search by Product Name, Variant Name, SKU, or Barcode
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('variants', function ($v) use ($search) {
                      $v->where('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('variant_name', 'like', "%{$search}%");
                  });
            });
        }

        $products = $query->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $products,
        ]);
    }

    /**
     * Store a newly created product with variants, units, prices, and initial warehouse stock.
     */
    public function store(ProductRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $product = DB::transaction(function () use ($validated, $request) {
            // 1. Buat Slug Produk yang Unik
            $slug = Str::slug($validated['name']);
            $originalSlug = $slug;
            $count = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = "{$originalSlug}-{$count}";
                $count++;
            }

            // 2. Buat Record Produk Induk
            $product = Product::create([
                'category_id' => $validated['category_id'] ?? null,
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'has_variants' => $validated['has_variants'] ?? (count($validated['variants']) > 1),
                'is_active' => true,
            ]);

            // 3. Kaitkan Base Unit
            ProductUnit::create([
                'product_id' => $product->id,
                'unit_id' => $validated['base_unit_id'],
                'conversion_factor' => 1,
                'is_base_unit' => true,
            ]);

            // 4. Kaitkan Satuan Tambahan Lainnya (jika ada)
            if (! empty($validated['units'])) {
                foreach ($validated['units'] as $u) {
                    if ($u['unit_id'] != $validated['base_unit_id']) {
                        ProductUnit::create([
                            'product_id' => $product->id,
                            'unit_id' => $u['unit_id'],
                            'conversion_factor' => $u['conversion_factor'],
                            'is_base_unit' => false,
                        ]);
                    }
                }
            }

            // Cari Gudang Pusat untuk alokasi stok awal
            $centralWarehouse = Branch::where('is_central_warehouse', true)->first() ?: Branch::first();

            // 5. Simpan Varian Produk, Harga, dan Stok Awal
            foreach ($validated['variants'] as $v) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => strtoupper(trim($v['sku'])),
                    'barcode' => ! empty($v['barcode']) ? trim($v['barcode']) : null,
                    'variant_name' => $v['variant_name'],
                    'attributes' => $v['attributes'] ?? null,
                    'purchase_price' => $v['purchase_price'],
                    'is_active' => true,
                ]);

                // Simpan Aturan Multi-Harga (Eceran & Grosir)
                foreach ($v['prices'] as $p) {
                    ProductPrice::create([
                        'product_variant_id' => $variant->id,
                        'unit_id' => $p['unit_id'],
                        'price_type' => $p['price_type'],
                        'min_quantity' => $p['min_quantity'],
                        'selling_price' => $p['selling_price'],
                    ]);
                }

                // Inisialisasi Stok Awal di Gudang Pusat jika diisi
                $initialStock = isset($v['initial_stock']) ? (float) $v['initial_stock'] : 0;
                if ($initialStock > 0 && $centralWarehouse) {
                    BranchStock::create([
                        'branch_id' => $centralWarehouse->id,
                        'product_variant_id' => $variant->id,
                        'quantity' => $initialStock,
                        'reserved_quantity' => 0,
                        'min_stock_alert' => 10,
                    ]);

                    // Catat Kartu Stok (Audit Trail)
                    StockMovement::create([
                        'branch_id' => $centralWarehouse->id,
                        'product_variant_id' => $variant->id,
                        'type' => 'in',
                        'quantity' => $initialStock,
                        'stock_before' => 0,
                        'stock_after' => $initialStock,
                        'notes' => 'Stok Awal Pendaftaran Produk Baru',
                        'user_id' => $request->user()?->id,
                    ]);
                }
            }

            return $product;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Produk dan varian berhasil didaftarkan.',
            'data' => $product->load(['category', 'productUnits.unit', 'variants.prices.unit', 'variants.branchStocks.branch']),
        ], 201);
    }

    /**
     * Display the specified product.
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with([
            'category',
            'productUnits.unit',
            'variants.prices.unit',
            'variants.branchStocks.branch',
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $product,
        ]);
    }

    /**
     * Update the specified product.
     */
    public function update(ProductRequest $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $validated = $request->validated();

        DB::transaction(function () use ($product, $validated) {
            // Update Data Produk Induk
            $product->update([
                'category_id' => $validated['category_id'] ?? null,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'has_variants' => $validated['has_variants'] ?? (count($validated['variants']) > 1),
            ]);

            // Update/Sync Units
            ProductUnit::where('product_id', $product->id)->delete();
            ProductUnit::create([
                'product_id' => $product->id,
                'unit_id' => $validated['base_unit_id'],
                'conversion_factor' => 1,
                'is_base_unit' => true,
            ]);

            if (! empty($validated['units'])) {
                foreach ($validated['units'] as $u) {
                    if ($u['unit_id'] != $validated['base_unit_id']) {
                        ProductUnit::create([
                            'product_id' => $product->id,
                            'unit_id' => $u['unit_id'],
                            'conversion_factor' => $u['conversion_factor'],
                            'is_base_unit' => false,
                        ]);
                    }
                }
            }

            // Update/Upsert Varian
            $existingVariantIds = [];
            foreach ($validated['variants'] as $v) {
                $variant = ProductVariant::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'sku' => strtoupper(trim($v['sku'])),
                    ],
                    [
                        'barcode' => ! empty($v['barcode']) ? trim($v['barcode']) : null,
                        'variant_name' => $v['variant_name'],
                        'attributes' => $v['attributes'] ?? null,
                        'purchase_price' => $v['purchase_price'],
                        'is_active' => true,
                    ]
                );

                $existingVariantIds[] = $variant->id;

                // Sync Harga
                ProductPrice::where('product_variant_id', $variant->id)->delete();
                foreach ($v['prices'] as $p) {
                    ProductPrice::create([
                        'product_variant_id' => $variant->id,
                        'unit_id' => $p['unit_id'],
                        'price_type' => $p['price_type'],
                        'min_quantity' => $p['min_quantity'],
                        'selling_price' => $p['selling_price'],
                    ]);
                }
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Produk dan varian berhasil diperbarui.',
            'data' => $product->fresh(['category', 'productUnits.unit', 'variants.prices.unit', 'variants.branchStocks.branch']),
        ]);
    }

    /**
     * Remove the specified product.
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::with('variants.saleItems')->findOrFail($id);

        // Proteksi: jangan hapus produk yang sudah memiliki riwayat penjualan
        foreach ($product->variants as $variant) {
            if ($variant->saleItems()->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Produk tidak dapat dihapus karena sudah memiliki riwayat transaksi penjualan.',
                ], 422);
            }
        }

        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil dihapus.',
        ]);
    }
}
