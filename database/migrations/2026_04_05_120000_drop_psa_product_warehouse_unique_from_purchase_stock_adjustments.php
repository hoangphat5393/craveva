<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove UNIQUE(product_id, warehouse_id) — it blocks valid data:
     * each PurchaseInventory document has its own lines; the same product in the same
     * warehouse may appear on many documents (imports, adjustments, batches).
     * Warehouse aggregates live in warehouse_* tables; this table is line-level.
     */
    public function up(): void
    {
        if (! Schema::hasTable('purchase_stock_adjustments')) {
            return;
        }

        if (! Schema::hasIndex('purchase_stock_adjustments', 'psa_product_warehouse_unique')) {
            return;
        }

        Schema::table('purchase_stock_adjustments', function (Blueprint $table) {
            $table->dropUnique('psa_product_warehouse_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('purchase_stock_adjustments')) {
            return;
        }

        if (Schema::hasIndex('purchase_stock_adjustments', 'psa_product_warehouse_unique')) {
            return;
        }

        Schema::table('purchase_stock_adjustments', function (Blueprint $table) {
            $table->unique(['product_id', 'warehouse_id'], 'psa_product_warehouse_unique');
        });
    }
};
