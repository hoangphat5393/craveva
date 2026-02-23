<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_order_items', 'purchase_item_id')) {
                $table->unsignedBigInteger('purchase_item_id')->nullable()->after('delivery_order_id');
            }

            if (!Schema::hasColumn('delivery_order_items', 'quantity_received')) {
                $table->double('quantity_received')->default(0)->after('quantity_ordered');
            }
        });
    }

    public function down(): void
    {
        // Intentionally left empty to avoid dropping columns in existing databases
    }
};
