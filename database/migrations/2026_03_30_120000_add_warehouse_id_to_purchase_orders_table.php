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
        if (! Schema::hasTable('purchase_orders') || Schema::hasColumn('purchase_orders', 'warehouse_id')) {
            return;
        }

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->nullable()->after('address_id');
            $table->index('warehouse_id', 'purchase_orders_warehouse_id_index');
            $table->foreign('warehouse_id', 'purchase_orders_warehouse_id_foreign')
                ->references('id')
                ->on('warehouses')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('purchase_orders') || ! Schema::hasColumn('purchase_orders', 'warehouse_id')) {
            return;
        }

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign('purchase_orders_warehouse_id_foreign');
            $table->dropIndex('purchase_orders_warehouse_id_index');
            $table->dropColumn('warehouse_id');
        });
    }
};
