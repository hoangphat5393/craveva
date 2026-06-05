<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Maolin / quotation extras (groups 2–4): master duplicates, logistics, OEM pointer, line meta.
     */
    public function up(): void
    {
        Schema::table('estimates', function (Blueprint $table): void {
            $columns = [
                'payment_terms_code',
                'payment_terms_name',
                'salesperson_name',
                'tax_type_label',
                'price_terms',
                'volume_unit',
                'total_gross_weight_kg',
                'total_volume',
                'delivery_note',
                'recipe_target_unit_price',
                'production_bom_id',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('estimates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('estimate_items', function (Blueprint $table): void {
            $columns = [
                'free_quantity',
                'line_effective_date',
                'line_expiry_date',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('estimate_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('estimates', function (Blueprint $table): void {
            if (! Schema::hasColumn('estimates', 'payment_terms_code')) {
                $table->string('payment_terms_code', 64)->nullable();
            }
            if (! Schema::hasColumn('estimates', 'payment_terms_name')) {
                $table->string('payment_terms_name', 255)->nullable();
            }
            if (! Schema::hasColumn('estimates', 'salesperson_name')) {
                $table->string('salesperson_name', 191)->nullable();
            }
            if (! Schema::hasColumn('estimates', 'tax_type_label')) {
                $table->string('tax_type_label', 191)->nullable();
            }
            if (! Schema::hasColumn('estimates', 'price_terms')) {
                $table->string('price_terms', 255)->nullable();
            }
            if (! Schema::hasColumn('estimates', 'volume_unit')) {
                $table->string('volume_unit', 64)->nullable();
            }
            if (! Schema::hasColumn('estimates', 'total_gross_weight_kg')) {
                $table->decimal('total_gross_weight_kg', 16, 4)->nullable();
            }
            if (! Schema::hasColumn('estimates', 'total_volume')) {
                $table->decimal('total_volume', 16, 4)->nullable();
            }
            if (! Schema::hasColumn('estimates', 'delivery_note')) {
                $table->text('delivery_note')->nullable();
            }
            if (! Schema::hasColumn('estimates', 'recipe_target_unit_price')) {
                $table->decimal('recipe_target_unit_price', 16, 4)->nullable();
            }
            if (! Schema::hasColumn('estimates', 'production_bom_id')) {
                $table->unsignedBigInteger('production_bom_id')->nullable();
            }
        });

        Schema::table('estimate_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('estimate_items', 'free_quantity')) {
                $table->decimal('free_quantity', 16, 4)->nullable();
            }
            if (! Schema::hasColumn('estimate_items', 'line_effective_date')) {
                $table->date('line_effective_date')->nullable();
            }
            if (! Schema::hasColumn('estimate_items', 'line_expiry_date')) {
                $table->date('line_expiry_date')->nullable();
            }
        });
    }
};
