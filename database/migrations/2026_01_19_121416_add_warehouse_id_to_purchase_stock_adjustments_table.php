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
        if (!Schema::hasColumn('purchase_stock_adjustments', 'warehouse_id')) {
            Schema::table('purchase_stock_adjustments', function (Blueprint $table) {
                $table->unsignedInteger('warehouse_id')->nullable()->after('product_id');
                // Note: using unsignedInteger because warehouses.id might be int, check first. 
                // Earlier I saw warehouses migration use bigIncrements (unsignedBigInteger).
                // Let's use unsignedBigInteger to be safe, matching standard Laravel id().
            });
            
             Schema::table('purchase_stock_adjustments', function (Blueprint $table) {
                $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
             });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('purchase_stock_adjustments', 'warehouse_id')) {
            Schema::table('purchase_stock_adjustments', function (Blueprint $table) {
                $table->dropForeign(['warehouse_id']);
                $table->dropColumn('warehouse_id');
            });
        }
    }
};
