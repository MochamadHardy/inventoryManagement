<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants');
            $table->foreignId('unit_id')->nullable()->constrained('units');
            $table->decimal('quantity', 15, 2);
            $table->decimal('unit_price', 15, 2); // Harga jual satuan
            $table->decimal('hpp', 15, 2); // Snapshot HPP modal saat transaksi
            $table->decimal('discount_per_item', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2); // (unit_price - discount) * quantity
            $table->decimal('profit', 15, 2);   // (unit_price - discount - hpp) * quantity
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
