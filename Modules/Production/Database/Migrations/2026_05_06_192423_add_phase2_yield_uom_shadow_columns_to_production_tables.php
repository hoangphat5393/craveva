<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('production_bom_items')) {
            Schema::table('production_bom_items', function (Blueprint $table): void {
                if (! Schema::hasColumn('production_bom_items', 'yield_factor')) {
                    $table->decimal('yield_factor', 10, 4)->nullable()->after('unit_id');
                }
            });
        }

        if (Schema::hasTable('production_order_bom_snapshot_items')) {
            Schema::table('production_order_bom_snapshot_items', function (Blueprint $table): void {
                if (! Schema::hasColumn('production_order_bom_snapshot_items', 'unit_id')) {
                    $table->unsignedBigInteger('unit_id')->nullable()->after('quantity_per_fg_unit');
                }
                if (! Schema::hasColumn('production_order_bom_snapshot_items', 'yield_factor')) {
                    $table->decimal('yield_factor', 10, 4)->nullable()->after('unit_id');
                }
                if (! Schema::hasColumn('production_order_bom_snapshot_items', 'quantity_per_fg_unit_base_shadow')) {
                    $table->decimal('quantity_per_fg_unit_base_shadow', 20, 6)->nullable()->after('yield_factor');
                }
            });
        }

        if (Schema::hasTable('production_batch_consumptions')) {
            Schema::table('production_batch_consumptions', function (Blueprint $table): void {
                if (! Schema::hasColumn('production_batch_consumptions', 'planned_quantity_shadow')) {
                    $table->decimal('planned_quantity_shadow', 20, 6)->nullable()->after('planned_quantity');
                }
                if (! Schema::hasColumn('production_batch_consumptions', 'shadow_basis')) {
                    $table->json('shadow_basis')->nullable()->after('planned_quantity_shadow');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('production_batch_consumptions')) {
            Schema::table('production_batch_consumptions', function (Blueprint $table): void {
                if (Schema::hasColumn('production_batch_consumptions', 'shadow_basis')) {
                    $table->dropColumn('shadow_basis');
                }
                if (Schema::hasColumn('production_batch_consumptions', 'planned_quantity_shadow')) {
                    $table->dropColumn('planned_quantity_shadow');
                }
            });
        }

        if (Schema::hasTable('production_order_bom_snapshot_items')) {
            Schema::table('production_order_bom_snapshot_items', function (Blueprint $table): void {
                if (Schema::hasColumn('production_order_bom_snapshot_items', 'quantity_per_fg_unit_base_shadow')) {
                    $table->dropColumn('quantity_per_fg_unit_base_shadow');
                }
                if (Schema::hasColumn('production_order_bom_snapshot_items', 'yield_factor')) {
                    $table->dropColumn('yield_factor');
                }
                if (Schema::hasColumn('production_order_bom_snapshot_items', 'unit_id')) {
                    $table->dropColumn('unit_id');
                }
            });
        }

        if (Schema::hasTable('production_bom_items')) {
            Schema::table('production_bom_items', function (Blueprint $table): void {
                if (Schema::hasColumn('production_bom_items', 'yield_factor')) {
                    $table->dropColumn('yield_factor');
                }
            });
        }
    }
};
