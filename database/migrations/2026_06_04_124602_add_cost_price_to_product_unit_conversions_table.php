<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_unit_conversions')) {
            return;
        }

        Schema::table('product_unit_conversions', function (Blueprint $table): void {
            if (! Schema::hasColumn('product_unit_conversions', 'cost_price')) {
                $table->decimal('cost_price', 20, 4)->nullable()->after('selling_price');
            }
        });

        if (! Schema::hasColumn('product_unit_conversions', 'cost_price') || ! Schema::hasTable('products')) {
            return;
        }

        DB::statement("
            UPDATE product_unit_conversions puc
            INNER JOIN products p ON p.id = puc.product_id AND p.company_id = puc.company_id
            SET puc.cost_price = puc.selling_price,
                puc.selling_price = NULL
            WHERE p.type IN ('raw_material', 'semi_finished', 'packaging')
              AND puc.selling_price IS NOT NULL
              AND puc.cost_price IS NULL
        ");
    }

    public function down(): void
    {
        // Keep rollback non-destructive for production safety.
    }
};
