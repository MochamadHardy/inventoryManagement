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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('product_variant_id')->constrained('product_variants');
            $table->enum('type', ['in', 'out', 'transfer_out', 'transfer_in', 'sale', 'adjustment']);
            $table->nullableMorphs('reference'); // reference_type & reference_id (Sale, StockTransfer, etc.)
            $table->decimal('quantity', 15, 2); // Kuantitas mutasi (+ untuk penambahan, - untuk pengurangan)
            $table->decimal('stock_before', 15, 2); // Saldo sebelum
            $table->decimal('stock_after', 15, 2);  // Saldo setelah
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
