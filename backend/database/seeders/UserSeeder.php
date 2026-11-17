<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $whCentral = Branch::where('code', 'WH-CENTRAL')->first();
        $brJkt = Branch::where('code', 'BR-JKT01')->first();
        $brBdg = Branch::where('code', 'BR-BDG01')->first();

        // 1. Super Admin (Owner Pusat) - memiliki hak akses penuh ke seluruh cabang
        User::updateOrCreate(
            ['email' => 'owner@pos.com'],
            [
                'name' => 'Budi Santoso (Owner)',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'branch_id' => null, // Super admin tidak terikat 1 cabang spesifik
                'phone' => '081234567890',
                'is_active' => true,
            ]
        );

        // 2. Kepala Gudang Pusat
        User::updateOrCreate(
            ['email' => 'gudang@pos.com'],
            [
                'name' => 'Ahmad Rifai (Kepala Gudang)',
                'password' => Hash::make('password123'),
                'role' => 'central_warehouse',
                'branch_id' => $whCentral?->id,
                'phone' => '081234567891',
                'is_active' => true,
            ]
        );

        // 3. Kasir Cabang Jakarta Selatan
        User::updateOrCreate(
            ['email' => 'kasir.jkt@pos.com'],
            [
                'name' => 'Siti Nurhaliza (Kasir Jakarta)',
                'password' => Hash::make('password123'),
                'role' => 'branch_cashier',
                'branch_id' => $brJkt?->id,
                'phone' => '081234567892',
                'is_active' => true,
            ]
        );

        // 4. Kasir Cabang Bandung
        User::updateOrCreate(
            ['email' => 'kasir.bdg@pos.com'],
            [
                'name' => 'Dedi Kurniawan (Kasir Bandung)',
                'password' => Hash::make('password123'),
                'role' => 'branch_cashier',
                'branch_id' => $brBdg?->id,
                'phone' => '081234567893',
                'is_active' => true,
            ]
        );
    }
}
