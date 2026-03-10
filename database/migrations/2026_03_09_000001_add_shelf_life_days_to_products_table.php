<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Shelf life in days (保存天數) for F&B products; used in expiry logic and reports.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'shelf_life_days')) {
                $table->unsignedInteger('shelf_life_days')->nullable()->after('employee_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'shelf_life_days')) {
                $table->dropColumn('shelf_life_days');
            }
        });
    }
};
