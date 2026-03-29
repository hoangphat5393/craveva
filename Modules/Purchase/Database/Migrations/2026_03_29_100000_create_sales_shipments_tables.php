<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sales_shipments')) {
            Schema::create('sales_shipments', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('warehouse_id');
                $table->string('shipment_number', 64);
                $table->date('shipment_date');
                $table->enum('status', ['draft', 'confirmed', 'shipped', 'delivered', 'cancelled'])->default('draft');
                $table->boolean('outbound_stock_applied')->default(false);
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
                $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
                $table->foreign('warehouse_id')->references('id')->on('warehouses')->restrictOnDelete();
                $table->index(['company_id', 'status', 'shipment_date'], 'sales_shipments_company_status_date_idx');
                $table->unique(['company_id', 'shipment_number'], 'sales_shipments_company_number_unique');
            });
        }

        if (! Schema::hasTable('sales_shipment_items')) {
            Schema::create('sales_shipment_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sales_shipment_id');
                $table->unsignedBigInteger('order_item_id');
                $table->unsignedInteger('product_id')->nullable();
                $table->decimal('quantity_ordered', 20, 4)->default(0);
                $table->decimal('quantity_shipped', 20, 4)->default(0);
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->string('batch_number', 191)->nullable();
                $table->timestamps();

                $table->foreign('sales_shipment_id')->references('id')->on('sales_shipments')->cascadeOnDelete();
                $table->foreign('order_item_id')->references('id')->on('order_items')->cascadeOnDelete();
                $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
                $table->foreign('unit_id')->references('id')->on('unit_types')->nullOnDelete();
                $table->index(['sales_shipment_id', 'order_item_id'], 'sales_shipment_items_shipment_item_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_shipment_items');
        Schema::dropIfExists('sales_shipments');
    }
};
