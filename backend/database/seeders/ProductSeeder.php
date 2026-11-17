<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\ProductUnit;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $catFashion = Category::where('slug', 'pakaian-fashion')->first();
        $catGadget = Category::where('slug', 'elektronik-gadget')->first();

        $pcs = Unit::where('short_name', 'pcs')->first();
        $lsn = Unit::where('short_name', 'lsn')->first();
        $dus = Unit::where('short_name', 'dus')->first();

        $whCentral = Branch::where('code', 'WH-CENTRAL')->first();
        $brJkt = Branch::where('code', 'BR-JKT01')->first();
        $brBdg = Branch::where('code', 'BR-BDG01')->first();
        $systemUser = User::where('role', 'super_admin')->first();

        // -------------------------------------------------------------
        // PRODUK 1: Kaos Polos Combed 30s (Multi-Varian Warna & Ukuran)
        // -------------------------------------------------------------
        $kaos = Product::updateOrCreate(
            ['slug' => 'kaos-polos-combed-30s'],
            [
                'category_id' => $catFashion?->id,
                'name' => 'Kaos Polos Combed 30s Premium',
                'description' => 'Bahan 100% Cotton Combed 30s reaktif, adem dan nyaman dipakai.',
                'has_variants' => true,
                'is_active' => true,
            ]
        );

        // Satuan Kaos: Pcs (Base = 1), Lusin (Factor = 12)
        ProductUnit::updateOrCreate(
            ['product_id' => $kaos->id, 'unit_id' => $pcs->id],
            ['conversion_factor' => 1, 'is_base_unit' => true]
        );
        ProductUnit::updateOrCreate(
            ['product_id' => $kaos->id, 'unit_id' => $lsn->id],
            ['conversion_factor' => 12, 'is_base_unit' => false]
        );

        // Varian Kaos: Hitam-M, Hitam-L, Putih-M, Putih-L
        $kaosVariants = [
            [
                'sku' => 'TSH-BLK-M',
                'barcode' => '8991001001',
                'variant_name' => 'Hitam - M',
                'attributes' => ['color' => 'Hitam', 'size' => 'M'],
                'purchase_price' => 28000,
                'retail_price' => 45000,
                'wholesale_price' => 38000, // min 12 pcs (1 lusin)
                'central_stock' => 500,
                'jkt_stock' => 40,
                'bdg_stock' => 4, // Di bawah min_stock_alert (10) -> LOW STOCK ALERT!
            ],
            [
                'sku' => 'TSH-BLK-L',
                'barcode' => '8991001002',
                'variant_name' => 'Hitam - L',
                'attributes' => ['color' => 'Hitam', 'size' => 'L'],
                'purchase_price' => 29000,
                'retail_price' => 47000,
                'wholesale_price' => 40000,
                'central_stock' => 450,
                'jkt_stock' => 35,
                'bdg_stock' => 15,
            ],
            [
                'sku' => 'TSH-WHT-M',
                'barcode' => '8991001003',
                'variant_name' => 'Putih - M',
                'attributes' => ['color' => 'Putih', 'size' => 'M'],
                'purchase_price' => 28000,
                'retail_price' => 45000,
                'wholesale_price' => 38000,
                'central_stock' => 300,
                'jkt_stock' => 25,
                'bdg_stock' => 6, // LOW STOCK ALERT!
            ],
            [
                'sku' => 'TSH-WHT-L',
                'barcode' => '8991001004',
                'variant_name' => 'Putih - L',
                'attributes' => ['color' => 'Putih', 'size' => 'L'],
                'purchase_price' => 29000,
                'retail_price' => 47000,
                'wholesale_price' => 40000,
                'central_stock' => 350,
                'jkt_stock' => 50,
                'bdg_stock' => 20,
            ],
        ];

        foreach ($kaosVariants as $vData) {
            $variant = ProductVariant::updateOrCreate(
                ['sku' => $vData['sku']],
                [
                    'product_id' => $kaos->id,
                    'barcode' => $vData['barcode'],
                    'variant_name' => $vData['variant_name'],
                    'attributes' => $vData['attributes'],
                    'purchase_price' => $vData['purchase_price'],
                    'is_active' => true,
                ]
            );

            // Harga Eceran (Min 1 pcs)
            ProductPrice::updateOrCreate(
                ['product_variant_id' => $variant->id, 'unit_id' => $pcs->id, 'price_type' => 'retail'],
                ['min_quantity' => 1, 'selling_price' => $vData['retail_price']]
            );

            // Harga Grosir (Min 12 pcs / 1 lusin)
            ProductPrice::updateOrCreate(
                ['product_variant_id' => $variant->id, 'unit_id' => $pcs->id, 'price_type' => 'wholesale'],
                ['min_quantity' => 12, 'selling_price' => $vData['wholesale_price']]
            );

            // Alokasi Stok Awal per Cabang & Catat Kartu Stok (Movement)
            $stockConfigs = [
                ['branch' => $whCentral, 'qty' => $vData['central_stock']],
                ['branch' => $brJkt, 'qty' => $vData['jkt_stock']],
                ['branch' => $brBdg, 'qty' => $vData['bdg_stock']],
            ];

            foreach ($stockConfigs as $cfg) {
                if ($cfg['branch']) {
                    BranchStock::updateOrCreate(
                        ['branch_id' => $cfg['branch']->id, 'product_variant_id' => $variant->id],
                        [
                            'quantity' => $cfg['qty'],
                            'reserved_quantity' => 0,
                            'min_stock_alert' => 10,
                        ]
                    );

                    StockMovement::create([
                        'branch_id' => $cfg['branch']->id,
                        'product_variant_id' => $variant->id,
                        'type' => 'in',
                        'quantity' => $cfg['qty'],
                        'stock_before' => 0,
                        'stock_after' => $cfg['qty'],
                        'notes' => 'Saldo Awal Sistem',
                        'user_id' => $systemUser?->id,
                    ]);
                }
            }
        }

        // -------------------------------------------------------------
        // PRODUK 2: Kabel Data Fast Charging Type-C (Non-Varian Kompleks)
        // -------------------------------------------------------------
        $kabel = Product::updateOrCreate(
            ['slug' => 'kabel-data-fast-charging-type-c'],
            [
                'category_id' => $catGadget?->id,
                'name' => 'Kabel Data Fast Charging Type-C 65W',
                'description' => 'Kabel data braided nylon 1 meter support quick charge 3.0 & power delivery.',
                'has_variants' => false,
                'is_active' => true,
            ]
        );

        ProductUnit::updateOrCreate(
            ['product_id' => $kabel->id, 'unit_id' => $pcs->id],
            ['conversion_factor' => 1, 'is_base_unit' => true]
        );

        $kabelVariant = ProductVariant::updateOrCreate(
            ['sku' => 'CBL-TYPC-1M'],
            [
                'product_id' => $kabel->id,
                'barcode' => '8992002001',
                'variant_name' => 'Default (Hitam 1M)',
                'attributes' => ['length' => '1 meter', 'color' => 'Hitam'],
                'purchase_price' => 12000,
                'is_active' => true,
            ]
        );

        ProductPrice::updateOrCreate(
            ['product_variant_id' => $kabelVariant->id, 'unit_id' => $pcs->id, 'price_type' => 'retail'],
            ['min_quantity' => 1, 'selling_price' => 25000]
        );

        ProductPrice::updateOrCreate(
            ['product_variant_id' => $kabelVariant->id, 'unit_id' => $pcs->id, 'price_type' => 'wholesale'],
            ['min_quantity' => 10, 'selling_price' => 20000]
        );

        $kabelStock = [
            ['branch' => $whCentral, 'qty' => 1000],
            ['branch' => $brJkt, 'qty' => 80],
            ['branch' => $brBdg, 'qty' => 5], // LOW STOCK!
        ];

        foreach ($kabelStock as $cfg) {
            if ($cfg['branch']) {
                BranchStock::updateOrCreate(
                    ['branch_id' => $cfg['branch']->id, 'product_variant_id' => $kabelVariant->id],
                    [
                        'quantity' => $cfg['qty'],
                        'reserved_quantity' => 0,
                        'min_stock_alert' => 10,
                    ]
                );

                StockMovement::create([
                    'branch_id' => $cfg['branch']->id,
                    'product_variant_id' => $kabelVariant->id,
                    'type' => 'in',
                    'quantity' => $cfg['qty'],
                    'stock_before' => 0,
                    'stock_after' => $cfg['qty'],
                    'notes' => 'Saldo Awal Sistem',
                    'user_id' => $systemUser?->id,
                ]);
            }
        }
    }
}
