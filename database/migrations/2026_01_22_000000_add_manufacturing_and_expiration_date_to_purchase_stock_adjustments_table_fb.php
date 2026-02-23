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
        Schema::table('purchase_stock_adjustments', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_stock_adjustments', 'manufacturing_date')) {
                $table->date('manufacturing_date')->nullable()->after('description');
            }
            if (!Schema::hasColumn('purchase_stock_adjustments', 'expiration_date')) {
                $table->date('expiration_date')->nullable()->after('manufacturing_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_stock_adjustments', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_stock_adjustments', 'manufacturing_date')) {
                $table->dropColumn('manufacturing_date');
            }
            if (Schema::hasColumn('purchase_stock_adjustments', 'expiration_date')) {
                $table->dropColumn('expiration_date');
            }
        });
    }
};
