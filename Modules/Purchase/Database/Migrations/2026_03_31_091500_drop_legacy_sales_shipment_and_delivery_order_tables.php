<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop child tables first to avoid FK errors.
        Schema::dropIfExists('sales_shipment_items');
        Schema::dropIfExists('sales_shipments');
        Schema::dropIfExists('delivery_order_items');
        Schema::dropIfExists('delivery_orders');
    }

    public function down(): void
    {
        // Legacy rollback scaffold only. Core columns are restored;
        // optional business-specific extensions from older migrations are not included.
        if (! Schema::hasTable('sales_shipments')) {
            Schema::create('sales_shipments', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id')->nullable();
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('warehouse_id')->nullable();
                $table->string('shipment_number')->nullable();
                $table->date('shipment_date')->nullable();
                $table->string('status')->default('draft');
                $table->boolean('outbound_stock_applied')->default(false);
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sales_shipment_items')) {
            Schema::create('sales_shipment_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sales_shipment_id');
                $table->unsignedBigInteger('order_item_id');
                $table->unsignedBigInteger('product_id')->nullable();
                $table->decimal('quantity_ordered', 20, 4)->default(0);
                $table->decimal('quantity_shipped', 20, 4)->default(0);
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->string('batch_number')->nullable();
                $table->timestamps();

                $table->foreign('sales_shipment_id')
                    ->references('id')
                    ->on('sales_shipments')
                    ->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('delivery_orders')) {
            Schema::create('delivery_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id')->nullable();
                $table->unsignedBigInteger('purchase_order_id')->nullable();
                $table->string('type')->nullable();
                $table->string('delivery_number')->nullable();
                $table->date('delivery_date')->nullable();
                $table->unsignedBigInteger('warehouse_id')->nullable();
                $table->string('status')->default('draft');
                $table->boolean('inbound_stock_applied')->default(false);
                $table->string('erp_shipment_reference')->nullable();
                $table->string('wms_shipment_reference')->nullable();
                $table->decimal('delivery_fee', 20, 4)->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('delivery_order_items')) {
            Schema::create('delivery_order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('delivery_order_id');
                $table->unsignedBigInteger('purchase_item_id')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('batch_number')->nullable();
                $table->date('expiry_date')->nullable();
                $table->string('picking_rule_applied')->nullable();
                $table->decimal('quantity_ordered', 20, 4)->default(0);
                $table->decimal('quantity_received', 20, 4)->default(0);
                $table->timestamps();

                $table->foreign('delivery_order_id')
                    ->references('id')
                    ->on('delivery_orders')
                    ->cascadeOnDelete();
            });
        }
    }
};
