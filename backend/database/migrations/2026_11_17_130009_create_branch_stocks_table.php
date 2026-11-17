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
        Schema::create('branch_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->decimal('quantity', 15, 2)->default(0); // Stok fisik tersedia (dalam base unit)
            $table->decimal('reserved_quantity', 15, 2)->default(0); // Stok terkunci saat proses mutasi
            $table->integer('min_stock_alert')->default(10); // Batas safety stock peringatan
            $table->timestamps();

            $table->unique(['branch_id', 'product_variant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_stocks');
    }
};
