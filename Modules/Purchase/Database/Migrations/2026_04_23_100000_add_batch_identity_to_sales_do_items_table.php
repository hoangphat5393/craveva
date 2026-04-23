<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sales_do_items')) {
            return;
        }

        Schema::table('sales_do_items', function (Blueprint $table) {
            if (! Schema::hasColumn('sales_do_items', 'warehouse_batch_id')) {
                $table->unsignedBigInteger('warehouse_batch_id')->nullable()->after('unit_id');
                $table->index(['sales_do_id', 'warehouse_batch_id'], 'sales_do_items_do_batch_idx');
            }

            if (! Schema::hasColumn('sales_do_items', 'expiration_date')) {
                $table->date('expiration_date')->nullable()->after('batch_number');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('sales_do_items')) {
            return;
        }

        Schema::table('sales_do_items', function (Blueprint $table) {
            if (Schema::hasColumn('sales_do_items', 'warehouse_batch_id')) {
                $table->dropIndex('sales_do_items_do_batch_idx');
                $table->dropColumn('warehouse_batch_id');
            }

            if (Schema::hasColumn('sales_do_items', 'expiration_date')) {
                $table->dropColumn('expiration_date');
            }
        });
    }
};
