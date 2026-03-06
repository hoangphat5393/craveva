<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('delivery_order_items')) {
            Schema::create('delivery_order_items', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('delivery_order_id');
                $table->unsignedBigInteger('purchase_order_item_id')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->string('batch_number')->nullable();
                $table->date('expiry_date')->nullable();
                $table->unsignedBigInteger('warehouse_location_id')->nullable();
                $table->decimal('quantity_ordered', 15, 4)->nullable();
                $table->decimal('quantity_delivered', 15, 4)->default(0);
                $table->string('picking_rule_applied', 10)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Không drop bảng để tránh mất dữ liệu production
    }
};
