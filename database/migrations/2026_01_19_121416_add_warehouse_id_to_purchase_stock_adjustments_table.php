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
                $table->unsignedBigInteger('warehouse_id')->nullable()->after('product_id');
                // Note: using unsignedBigInteger because warehouses.id is bigIncrements (unsignedBigInteger).
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
