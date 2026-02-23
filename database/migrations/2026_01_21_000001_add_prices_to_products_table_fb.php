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
            if (!Schema::hasColumn('products', 'wholesale_price')) {
                $table->double('wholesale_price')->nullable()->after('price');
            }
            if (!Schema::hasColumn('products', 'price_per_box')) {
                $table->double('price_per_box')->nullable()->after('wholesale_price');
            }
            if (!Schema::hasColumn('products', 'employee_price')) {
                $table->double('employee_price')->nullable()->after('price_per_box');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'wholesale_price')) {
                $table->dropColumn('wholesale_price');
            }
            if (Schema::hasColumn('products', 'price_per_box')) {
                $table->dropColumn('price_per_box');
            }
            if (Schema::hasColumn('products', 'employee_price')) {
                $table->dropColumn('employee_price');
            }
        });
    }
};
