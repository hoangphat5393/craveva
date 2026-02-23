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
            if (Schema::hasColumn('products', 'wholesale_price')) {
                $table->double('wholesale_price')->nullable()->change();
            }
            if (Schema::hasColumn('products', 'price_per_box')) {
                $table->double('price_per_box')->nullable()->change();
            }
            if (Schema::hasColumn('products', 'employee_price')) {
                $table->double('employee_price')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left empty to avoid data loss or strictness issues on rollback
    }
};
