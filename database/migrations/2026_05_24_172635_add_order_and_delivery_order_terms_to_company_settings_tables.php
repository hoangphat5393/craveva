<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoice_settings') && ! Schema::hasColumn('invoice_settings', 'order_terms')) {
            Schema::table('invoice_settings', function (Blueprint $table) {
                $table->text('order_terms')->nullable()->after('invoice_terms');
            });
        }

        if (Schema::hasTable('purchase_settings') && ! Schema::hasColumn('purchase_settings', 'delivery_order_terms')) {
            Schema::table('purchase_settings', function (Blueprint $table) {
                $table->text('delivery_order_terms')->nullable()->after('purchase_terms');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invoice_settings') && Schema::hasColumn('invoice_settings', 'order_terms')) {
            Schema::table('invoice_settings', function (Blueprint $table) {
                $table->dropColumn('order_terms');
            });
        }

        if (Schema::hasTable('purchase_settings') && Schema::hasColumn('purchase_settings', 'delivery_order_terms')) {
            Schema::table('purchase_settings', function (Blueprint $table) {
                $table->dropColumn('delivery_order_terms');
            });
        }
    }
};
