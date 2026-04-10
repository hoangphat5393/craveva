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
        if (Schema::hasTable('warehouse_company_flow_settings')) {
            return;
        }

        Schema::create('warehouse_company_flow_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->boolean('allow_negative_stock')->default(false);
            $table->boolean('strict_unit_conversion')->default(false);
            $table->boolean('inbound_from_purchase_order_delivered')->default(true);
            $table->boolean('inbound_from_delivery_order_received')->default(false);
            $table->boolean('sales_outbound_enabled')->default(true);
            $table->string('sales_outbound_mode', 32)->default('shipment');
            $table->boolean('ai_order_webhook_check_stock')->default(true);
            $table->timestamps();

            $table->unique('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_company_flow_settings');
    }
};
