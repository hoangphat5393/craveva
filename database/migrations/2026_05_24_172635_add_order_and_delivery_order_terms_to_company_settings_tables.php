<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        if (Schema::hasTable('purchase_settings') && ! Schema::hasColumn('purchase_settings', 'grn_terms')) {
            Schema::table('purchase_settings', function (Blueprint $table) {
                $table->text('grn_terms')->nullable()->after('purchase_terms');
            });
        }

        if (Schema::hasTable('purchase_settings') && Schema::hasColumn('purchase_settings', 'grn_terms')) {
            DB::table('purchase_settings')
                ->where(function ($query): void {
                    $query->whereNull('purchase_terms')->orWhere('purchase_terms', '');
                })
                ->whereNotNull('grn_terms')
                ->where('grn_terms', '!=', '')
                ->update(['purchase_terms' => DB::raw('grn_terms')]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invoice_settings') && Schema::hasColumn('invoice_settings', 'order_terms')) {
            Schema::table('invoice_settings', function (Blueprint $table) {
                $table->dropColumn('order_terms');
            });
        }

        if (Schema::hasTable('purchase_settings') && Schema::hasColumn('purchase_settings', 'grn_terms')) {
            Schema::table('purchase_settings', function (Blueprint $table) {
                $table->dropColumn('grn_terms');
            });
        }
    }
};
