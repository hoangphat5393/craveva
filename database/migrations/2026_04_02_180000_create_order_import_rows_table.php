<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_import_rows')) {
            return;
        }

        Schema::create('order_import_rows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('order_item_id')->nullable()->index();
            $table->string('source_hash', 64);
            $table->date('shipment_date')->nullable();
            $table->string('customer_code', 191)->nullable();
            $table->string('product_sku', 191)->nullable();
            $table->decimal('net_sales_volume', 30, 6)->nullable();
            $table->decimal('net_sales_amount', 30, 6)->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'source_hash'], 'order_import_rows_company_hash_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_import_rows');
    }
};
