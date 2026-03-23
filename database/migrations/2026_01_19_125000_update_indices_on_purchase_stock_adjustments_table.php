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
        // Laravel 11: Connection::getDoctrineSchemaManager() đã bỏ — dùng Schema::hasIndex()
        if (Schema::hasIndex('purchase_stock_adjustments', 'psa_product_warehouse_unique')) {
            return;
        }

        Schema::table('purchase_stock_adjustments', function (Blueprint $table) {
            $table->unique(['product_id', 'warehouse_id'], 'psa_product_warehouse_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_stock_adjustments', function (Blueprint $table) {
            $table->dropUnique('psa_product_warehouse_unique');
        });
    }
};
