<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'inventory_type')) {
                $table->string('inventory_type')->nullable()->after('status');
            }
            if (!Schema::hasColumn('products', 'wholesale_price')) {
                $table->decimal('wholesale_price', 16, 2)->default(0)->after('price');
            }
            if (!Schema::hasColumn('products', 'price_per_box')) {
                $table->decimal('price_per_box', 16, 2)->default(0)->after('wholesale_price');
            }
            if (!Schema::hasColumn('products', 'employee_price')) {
                $table->decimal('employee_price', 16, 2)->default(0)->after('price_per_box');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $columns = ['inventory_type', 'wholesale_price', 'price_per_box', 'employee_price'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
