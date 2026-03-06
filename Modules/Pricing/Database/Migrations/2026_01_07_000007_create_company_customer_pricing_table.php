<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('company_customer_pricing')) {
            Schema::create('company_customer_pricing', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('company_id')->index();
                $table->unsignedInteger('customer_company_id')->index();
                $table->unsignedBigInteger('pricing_tier_id')->nullable()->index();
                $table->enum('custom_discount_type', ['percentage', 'fixed_amount'])->nullable();
                $table->decimal('custom_discount_value', 15, 4)->nullable();
                $table->boolean('is_active')->default(true);
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('customer_company_id')->references('id')->on('companies')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('pricing_tier_id')->references('id')->on('pricing_tiers')->onUpdate('cascade')->onDelete('set null');
                $table->unique(['company_id', 'customer_company_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_customer_pricing');
    }
};
