<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('grns')) {
            Schema::create('grns', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('legacy_delivery_order_id')->nullable()->unique('grns_legacy_unique');
                $table->unsignedInteger('company_id')->nullable();
                $table->unsignedBigInteger('purchase_order_id')->nullable();
                $table->string('type')->nullable();
                $table->string('grn_number')->nullable();
                $table->date('grn_date')->nullable();
                $table->unsignedBigInteger('warehouse_id')->nullable();
                $table->string('status')->default('draft');
                $table->boolean('inbound_stock_applied')->default(false);
                $table->string('erp_shipment_reference')->nullable();
                $table->string('wms_shipment_reference')->nullable();
                $table->decimal('delivery_fee', 20, 4)->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'status', 'grn_date'], 'grns_company_status_date_idx');
            });
        }

        if (! Schema::hasTable('grn_items')) {
            Schema::create('grn_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('grn_id');
                $table->unsignedBigInteger('legacy_delivery_order_item_id')->nullable()->unique('grn_items_legacy_unique');
                $table->unsignedBigInteger('purchase_item_id')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('batch_number')->nullable();
                $table->date('expiry_date')->nullable();
                $table->string('picking_rule_applied')->nullable();
                $table->decimal('quantity_ordered', 20, 4)->default(0);
                $table->decimal('quantity_received', 20, 4)->default(0);
                $table->timestamps();

                $table->foreign('grn_id')->references('id')->on('grns')->cascadeOnDelete();
                $table->index(['grn_id', 'purchase_item_id'], 'grn_items_grn_item_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('grn_items');
        Schema::dropIfExists('grns');
    }
};
