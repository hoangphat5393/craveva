<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('production_orders', 'bom_snapshot_at')) {
                $table->timestamp('bom_snapshot_at')->nullable()->after('completed_at');
            }
            if (! Schema::hasColumn('production_orders', 'bom_snapshot_planned_quantity')) {
                $table->decimal('bom_snapshot_planned_quantity', 15, 4)->nullable()->after('bom_snapshot_at');
            }
        });

        if (! Schema::hasTable('production_order_bom_snapshot_items')) {
            Schema::create('production_order_bom_snapshot_items', function (Blueprint $table): void {
                $table->id();
                $table->unsignedInteger('company_id')->nullable();
                $table->unsignedBigInteger('production_order_id');
                $table->unsignedInteger('component_product_id');
                $table->decimal('quantity_per_fg_unit', 15, 4);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete()->cascadeOnUpdate();
                $table->foreign('production_order_id')->references('id')->on('production_orders')->cascadeOnDelete()->cascadeOnUpdate();
                $table->foreign('component_product_id')->references('id')->on('products')->cascadeOnDelete()->cascadeOnUpdate();
                $table->index(['production_order_id', 'sort_order'], 'production_order_bom_snap_items_order_sort_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('production_order_bom_snapshot_items');

        Schema::table('production_orders', function (Blueprint $table): void {
            if (Schema::hasColumn('production_orders', 'bom_snapshot_planned_quantity')) {
                $table->dropColumn('bom_snapshot_planned_quantity');
            }
            if (Schema::hasColumn('production_orders', 'bom_snapshot_at')) {
                $table->dropColumn('bom_snapshot_at');
            }
        });
    }
};
