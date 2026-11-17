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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'central_warehouse', 'branch_cashier'])->default('branch_cashier')->after('email');
            $table->foreignId('branch_id')->nullable()->after('role')->constrained('branches')->nullOnDelete();
            $table->string('phone', 30)->nullable()->after('branch_id');
            $table->boolean('is_active')->default(true)->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['role', 'branch_id', 'phone', 'is_active']);
        });
    }
};
