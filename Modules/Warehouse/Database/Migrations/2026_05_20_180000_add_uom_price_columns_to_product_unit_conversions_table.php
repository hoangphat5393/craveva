<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_unit_conversions')) {
            return;
        }

        Schema::table('product_unit_conversions', function (Blueprint $table): void {
            if (! Schema::hasColumn('product_unit_conversions', 'selling_price')) {
                $table->decimal('selling_price', 20, 4)->nullable()->after('factor_to_base');
            }
            if (! Schema::hasColumn('product_unit_conversions', 'for_sale')) {
                $table->boolean('for_sale')->default(true)->after('selling_price');
            }
            if (! Schema::hasColumn('product_unit_conversions', 'sort_order')) {
                $table->unsignedSmallInteger('sort_order')->default(0)->after('for_sale');
            }
        });
    }

    public function down(): void
    {
        // Keep rollback non-destructive for production safety.
    }
};
