<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('client_product_pricing')) {
            Schema::create('client_product_pricing', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('company_id')->nullable()->index();
                $table->unsignedInteger('client_id');
                $table->unsignedInteger('product_id');
                $table->decimal('custom_price', 15, 2)->nullable();
                $table->enum('discount_type', ['percentage', 'fixed'])->nullable();
                $table->decimal('discount_value', 15, 2)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('client_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('product_id')->references('id')->on('products')->onUpdate('cascade')->onDelete('cascade');
                $table->unique(['client_id', 'product_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('client_product_pricing');
    }
};
