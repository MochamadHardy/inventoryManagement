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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique(); // Misal: INV-20261117-0001
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('cashier_id')->constrained('users');
            $table->string('customer_name')->default('Pelanggan Umum');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('final_amount', 15, 2); // Nilai yang harus dibayar
            $table->decimal('paid_amount', 15, 2);  // Nilai yang dibayarkan pelanggan
            $table->decimal('change_amount', 15, 2)->default(0); // Uang kembalian
            $table->enum('payment_method', ['cash', 'qris', 'transfer', 'debit'])->default('cash');
            $table->enum('payment_status', ['paid', 'partial', 'unpaid'])->default('paid');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
