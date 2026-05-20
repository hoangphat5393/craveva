<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('production_bom_items') && ! Schema::hasColumn('production_bom_items', 'waste_percent')) {
            Schema::table('production_bom_items', function (Blueprint $table): void {
                $table->decimal('waste_percent', 8, 4)->default(0)->after('quantity');
            });
        }

        if (Schema::hasTable('production_order_bom_snapshot_items') && ! Schema::hasColumn('production_order_bom_snapshot_items', 'waste_percent')) {
            Schema::table('production_order_bom_snapshot_items', function (Blueprint $table): void {
                $table->decimal('waste_percent', 8, 4)->default(0)->after('quantity_per_fg_unit');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('production_bom_items') && Schema::hasColumn('production_bom_items', 'waste_percent')) {
            Schema::table('production_bom_items', function (Blueprint $table): void {
                $table->dropColumn('waste_percent');
            });
        }

        if (Schema::hasTable('production_order_bom_snapshot_items') && Schema::hasColumn('production_order_bom_snapshot_items', 'waste_percent')) {
            Schema::table('production_order_bom_snapshot_items', function (Blueprint $table): void {
                $table->dropColumn('waste_percent');
            });
        }
    }
};
