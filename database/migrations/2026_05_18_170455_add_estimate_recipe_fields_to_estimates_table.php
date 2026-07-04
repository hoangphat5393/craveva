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
                $table->unsignedInteger('recipe_moq')->nullable()->after('valid_till');
            }

            if (! Schema::hasColumn('estimates', 'recipe_packaging')) {
                $table->string('recipe_packaging', 255)->nullable()->after('recipe_moq');
            }

            if (! Schema::hasColumn('estimates', 'recipe_oem_sku')) {
                $table->string('recipe_oem_sku', 128)->nullable()->after('recipe_packaging');
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
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('estimates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
