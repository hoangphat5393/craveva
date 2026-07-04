<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('production_order_bom_snapshot_items')) {
            return;
        }

        Schema::table('production_order_bom_snapshot_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('production_order_bom_snapshot_items', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('waste_percent');
            }

            if (! Schema::hasColumn('production_order_bom_snapshot_items', 'yield_factor')) {
                $table->decimal('yield_factor', 10, 4)->nullable()->after('unit_id');
            }

            if (! Schema::hasColumn('production_order_bom_snapshot_items', 'quantity_per_fg_unit_base_shadow')) {
                $table->decimal('quantity_per_fg_unit_base_shadow', 20, 6)->nullable()->after('yield_factor');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('production_order_bom_snapshot_items')) {
            return;
        }

        Schema::table('production_order_bom_snapshot_items', function (Blueprint $table): void {
            $columns = [
                'quantity_per_fg_unit_base_shadow',
                'yield_factor',
                'unit_id',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('production_order_bom_snapshot_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
