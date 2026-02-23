<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('pricing_tier_items')) {
            Schema::create('pricing_tier_items', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('pricing_tier_id');
                $table->unsignedInteger('product_id');
                $table->enum('discount_type', ['percentage', 'fixed', 'specific_price']);
                $table->decimal('discount_value', 15, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('pricing_tier_id')->references('id')->on('pricing_tiers')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('product_id')->references('id')->on('products')->onUpdate('cascade')->onDelete('cascade');
                $table->unique(['pricing_tier_id', 'product_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('pricing_tier_items');
    }
};
