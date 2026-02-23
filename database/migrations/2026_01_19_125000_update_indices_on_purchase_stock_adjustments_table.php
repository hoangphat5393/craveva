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
        Schema::table('purchase_stock_adjustments', function (Blueprint $table) {
            // Add composite unique index if it doesn't exist
            // We use a raw check because Schema::hasIndex is not reliable for named indices in all versions
            // But we can just try to add it. If it exists, it might throw error.
            // Better to use Schema manager.
            
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('purchase_stock_adjustments');
            
            if (!array_key_exists('psa_product_warehouse_unique', $indexes)) {
                 $table->unique(['product_id', 'warehouse_id'], 'psa_product_warehouse_unique');
            }
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
