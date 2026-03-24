<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional traceability: link invoice lines to fulfillment (DO line) — does not affect stock math.
     */
    public function up(): void
    {
        if (! Schema::hasTable('invoice_items')) {
            return;
        }

        Schema::table('invoice_items', function (Blueprint $table) {
            if (! Schema::hasColumn('invoice_items', 'delivery_order_item_id')) {
                $table->unsignedBigInteger('delivery_order_item_id')->nullable()->after('product_id');
                $table->index('delivery_order_item_id', 'invoice_items_delivery_order_item_idx');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('invoice_items')) {
            return;
        }

        Schema::table('invoice_items', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_items', 'delivery_order_item_id')) {
                $table->dropIndex('invoice_items_delivery_order_item_idx');
                $table->dropColumn('delivery_order_item_id');
            }
        });
    }
};
