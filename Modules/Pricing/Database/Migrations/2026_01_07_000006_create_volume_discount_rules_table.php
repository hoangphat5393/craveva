<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('volume_discount_rules')) {
            Schema::create('volume_discount_rules', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('company_id')->nullable()->index();
                $table->unsignedBigInteger('pricing_tier_id')->nullable()->index();
                $table->string('name');
                $table->enum('discount_type', ['percentage', 'fixed_amount', 'tiered']);
                $table->unsignedInteger('minimum_quantity');
                $table->unsignedInteger('maximum_quantity')->nullable();
                $table->decimal('discount_value', 15, 4)->nullable();
                $table->unsignedInteger('applies_to_product_id')->nullable()->index();
                $table->unsignedBigInteger('applies_to_category_id')->nullable()->index();
                $table->enum('applies_to_type', ['all', 'products', 'services', 'specific'])->default('all');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('pricing_tier_id')->references('id')->on('pricing_tiers')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('applies_to_product_id')->references('id')->on('products')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('applies_to_category_id')->references('id')->on('product_category')->onUpdate('cascade')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('volume_discount_rules');
    }
};
