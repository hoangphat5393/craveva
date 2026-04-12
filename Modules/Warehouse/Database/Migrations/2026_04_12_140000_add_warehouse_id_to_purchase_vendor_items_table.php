<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('purchase_vendor_items')) {
            return;
        }

        if (! Schema::hasColumn('purchase_vendor_items', 'warehouse_id')) {
            Schema::table('purchase_vendor_items', function (Blueprint $table) {
                $table->unsignedBigInteger('warehouse_id')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchase_vendor_items') && Schema::hasColumn('purchase_vendor_items', 'warehouse_id')) {
            Schema::table('purchase_vendor_items', function (Blueprint $table) {
                $table->dropColumn('warehouse_id');
            });
        }
    }
};
