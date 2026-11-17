<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Branch::updateOrCreate(
            ['code' => 'WH-CENTRAL'],
            [
                'name' => 'Gudang Pusat Logistik',
                'phone' => '021-5550001',
                'address' => 'Kawasan Industri Pulogadung No. 10, Jakarta Timur',
                'is_central_warehouse' => true,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'BR-JKT01'],
            [
                'name' => 'Toko Cabang Jakarta Selatan',
                'phone' => '021-7770002',
                'address' => 'Jl. Fatmawati Raya No. 45, Cilandak, Jakarta Selatan',
                'is_central_warehouse' => false,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'BR-BDG01'],
            [
                'name' => 'Toko Cabang Bandung Dago',
                'phone' => '022-8880003',
                'address' => 'Jl. Ir. H. Juanda No. 120, Dago, Bandung',
                'is_central_warehouse' => false,
                'is_active' => true,
            ]
        );
    }
}
