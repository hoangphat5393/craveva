<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->unsignedBigInteger('delivery_order_item_id')->nullable();
                $table->string('movement_type', 20); // inbound, outbound, transfer, adjustment
                $table->unsignedBigInteger('warehouse_from_id')->nullable();
                $table->unsignedBigInteger('warehouse_to_id')->nullable();
                $table->unsignedBigInteger('warehouse_location_from_id')->nullable();
                $table->unsignedBigInteger('warehouse_location_to_id')->nullable();
                $table->string('batch_number')->nullable();
                $table->date('expiry_date')->nullable();
                $table->decimal('quantity', 15, 4);
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->string('fefo_fifo_rule', 10)->nullable(); // FIFO, FEFO
                $table->string('reference_type')->nullable(); // PO, Invoice, DO, Adjustment
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Không drop bảng để tránh mất dữ liệu production
    }
};

