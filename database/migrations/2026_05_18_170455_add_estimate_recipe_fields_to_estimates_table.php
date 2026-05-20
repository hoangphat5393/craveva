<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimates', function (Blueprint $table): void {
            if (! Schema::hasColumn('estimates', 'recipe_moq')) {
                $table->unsignedInteger('recipe_moq')->nullable()->after('total_volume');
            }

            if (! Schema::hasColumn('estimates', 'recipe_packaging')) {
                $table->string('recipe_packaging', 255)->nullable()->after('recipe_moq');
            }

            if (! Schema::hasColumn('estimates', 'recipe_oem_sku')) {
                $table->string('recipe_oem_sku', 128)->nullable()->after('recipe_packaging');
            }

            if (! Schema::hasColumn('estimates', 'recipe_target_unit_price')) {
                $table->decimal('recipe_target_unit_price', 16, 4)->nullable()->after('recipe_oem_sku');
            }
        });
    }

    public function down(): void
    {
        Schema::table('estimates', function (Blueprint $table): void {
            $columns = [
                'recipe_moq',
                'recipe_packaging',
                'recipe_oem_sku',
                'recipe_target_unit_price',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('estimates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
