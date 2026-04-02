<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_unit_conversions')) {
            return;
        }

        Schema::create('product_unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')->nullable()->index();
            $table->unsignedInteger('product_id');
            $table->unsignedBigInteger('unit_id');
            $table->decimal('factor_to_base', 20, 8)->default(1);
            $table->timestamps();

            $table->unique(['company_id', 'product_id', 'unit_id'], 'puc_company_product_unit_unique');
            $table->index(['company_id', 'product_id'], 'puc_company_product_idx');
        });
    }

    public function down(): void
    {
        // Keep rollback non-destructive for production safety.
    }
};
