<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('delivery_orders')) {
            Schema::create('delivery_orders', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('purchase_order_id')->nullable();
                $table->string('type', 20)->default('inbound');
                $table->string('delivery_number')->nullable();
                $table->date('delivery_date')->nullable();
                $table->unsignedBigInteger('warehouse_id')->nullable();
                $table->string('status', 20)->default('draft');
                $table->string('erp_shipment_reference')->nullable();
                $table->string('wms_shipment_reference')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Không drop bảng để tránh mất dữ liệu production
    }
};
