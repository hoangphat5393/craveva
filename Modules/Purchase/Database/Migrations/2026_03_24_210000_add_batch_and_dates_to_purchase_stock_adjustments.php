<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('purchase_stock_adjustments')) {
            return;
        }

        Schema::table('purchase_stock_adjustments', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_stock_adjustments', 'batch_number')) {
                $table->string('batch_number', 191)->nullable()->after('warehouse_id');
            }
            if (! Schema::hasColumn('purchase_stock_adjustments', 'manufacturing_date')) {
                $table->date('manufacturing_date')->nullable()->after('batch_number');
            }
            if (! Schema::hasColumn('purchase_stock_adjustments', 'expiration_date')) {
                $table->date('expiration_date')->nullable()->after('manufacturing_date');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('purchase_stock_adjustments')) {
            return;
        }

        Schema::table('purchase_stock_adjustments', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_stock_adjustments', 'batch_number')) {
                $table->dropColumn('batch_number');
            }
            // Keep existing date columns in rollback if they existed before this migration.
        });
    }
};
