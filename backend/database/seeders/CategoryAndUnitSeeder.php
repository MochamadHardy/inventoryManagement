<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoryAndUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Satuan Unit Dasar & Turunan
        $units = [
            ['name' => 'Pieces', 'short_name' => 'pcs'],
            ['name' => 'Lusin', 'short_name' => 'lsn'],
            ['name' => 'Dus / Box', 'short_name' => 'dus'],
            ['name' => 'Pack', 'short_name' => 'pck'],
            ['name' => 'Meter', 'short_name' => 'm'],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(['short_name' => $unit['short_name']], $unit);
        }

        // 2. Kategori Produk
        $categories = [
            ['name' => 'Pakaian & Fashion', 'description' => 'Produk pakaian pria, wanita, dan aksesoris fashion'],
            ['name' => 'Elektronik & Gadget', 'description' => 'Aksesoris smartphone, kabel data, charger'],
            ['name' => 'Makanan & Minuman', 'description' => 'Minuman kemasan, snack, kebutuhan konsumsi'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
