<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stock_movements')) {
            return;
        }

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index(['company_id', 'movement_type', 'created_at'], 'stock_movements_company_type_created_idx');
            $table->index(['warehouse_from_id', 'warehouse_to_id', 'product_id'], 'stock_movements_wh_from_to_product_idx');
            $table->index(['product_id', 'batch_number', 'expiry_date'], 'stock_movements_product_batch_expiry_idx');
            $table->index(['reference_type', 'reference_id'], 'stock_movements_reference_idx');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('stock_movements')) {
            return;
        }

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('stock_movements_company_type_created_idx');
            $table->dropIndex('stock_movements_wh_from_to_product_idx');
            $table->dropIndex('stock_movements_product_batch_expiry_idx');
            $table->dropIndex('stock_movements_reference_idx');
        });
    }
};
