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
            if (! Schema::hasColumn('purchase_stock_adjustments', 'reserved_quantity')) {
                $table->decimal('reserved_quantity', 15, 4)->nullable()->default(0)->after('net_quantity');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('purchase_stock_adjustments')) {
            return;
        }

        Schema::table('purchase_stock_adjustments', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_stock_adjustments', 'reserved_quantity')) {
                $table->dropColumn('reserved_quantity');
            }
        });
    }
};
