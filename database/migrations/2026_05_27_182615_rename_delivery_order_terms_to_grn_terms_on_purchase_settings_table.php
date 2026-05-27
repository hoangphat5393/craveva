<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('purchase_settings')) {
            return;
        }

        if (Schema::hasColumn('purchase_settings', 'grn_terms')) {
            if (Schema::hasColumn('purchase_settings', 'delivery_order_terms')) {
                DB::table('purchase_settings')
                    ->whereNull('grn_terms')
                    ->whereNotNull('delivery_order_terms')
                    ->update([
                        'grn_terms' => DB::raw('delivery_order_terms'),
                    ]);

                Schema::table('purchase_settings', function (Blueprint $table) {
                    $table->dropColumn('delivery_order_terms');
                });
            }

            return;
        }

        if (Schema::hasColumn('purchase_settings', 'delivery_order_terms')) {
            Schema::table('purchase_settings', function (Blueprint $table) {
                $table->renameColumn('delivery_order_terms', 'grn_terms');
            });

            return;
        }

        Schema::table('purchase_settings', function (Blueprint $table) {
            $table->text('grn_terms')->nullable()->after('purchase_terms');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('purchase_settings')) {
            return;
        }

        if (Schema::hasColumn('purchase_settings', 'delivery_order_terms')) {
            if (Schema::hasColumn('purchase_settings', 'grn_terms')) {
                Schema::table('purchase_settings', function (Blueprint $table) {
                    $table->dropColumn('grn_terms');
                });
            }

            return;
        }

        if (Schema::hasColumn('purchase_settings', 'grn_terms')) {
            Schema::table('purchase_settings', function (Blueprint $table) {
                $table->renameColumn('grn_terms', 'delivery_order_terms');
            });
        }
    }
};
