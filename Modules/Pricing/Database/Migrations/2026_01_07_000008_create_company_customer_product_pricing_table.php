<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('company_customer_product_pricing')) {
            Schema::create('company_customer_product_pricing', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('company_customer_pricing_id')->index();
                $table->unsignedInteger('product_id');
                $table->decimal('custom_price', 15, 4)->nullable();
                $table->enum('custom_discount_type', ['percentage', 'fixed_amount'])->nullable();
                $table->decimal('custom_discount_value', 15, 4)->nullable();
                $table->timestamps();

                $table->foreign('company_customer_pricing_id', 'ccpp_cc_pricing_id_fk')
                    ->references('id')
                    ->on('company_customer_pricing')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
                $table->foreign('product_id')->references('id')->on('products')->onUpdate('cascade')->onDelete('cascade');
                $table->unique(['company_customer_pricing_id', 'product_id'], 'ccpp_unique_pricing_product');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_customer_product_pricing');
    }
};
