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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku', 50)->unique();
            $table->string('barcode', 50)->unique()->nullable();
            $table->string('variant_name'); // e.g. "Merah - L" atau "Standard"
            $table->json('attributes')->nullable(); // e.g. {"color": "Merah", "size": "L"}
            $table->decimal('purchase_price', 15, 2)->default(0); // HPP dasar modal
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
