<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

function createProductionPostingSchema(): void
{
    Schema::create('production_boms', function (Blueprint $table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedInteger('output_product_id');
        $table->string('version', 32);
        $table->string('code', 64)->nullable();
        $table->date('effective_from')->nullable();
        $table->date('effective_to')->nullable();
        $table->boolean('is_default')->default(false);
        $table->text('notes')->nullable();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->timestamps();
    });

    Schema::create('production_bom_items', function (Blueprint $table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('production_bom_id');
        $table->unsignedInteger('component_product_id');
        $table->decimal('quantity', 15, 4);
        $table->decimal('waste_percent', 8, 4)->default(0);
        $table->unsignedBigInteger('unit_id')->nullable();
        $table->decimal('yield_factor', 10, 4)->nullable();
        $table->unsignedInteger('sort_order')->default(0);
        $table->timestamps();
    });

    Schema::create('production_orders', function (Blueprint $table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->string('status', 32)->default('draft');
        $table->unsignedInteger('output_product_id');
        $table->unsignedBigInteger('production_bom_id')->nullable();
        $table->unsignedBigInteger('rm_warehouse_id');
        $table->unsignedBigInteger('fg_warehouse_id');
        $table->decimal('planned_quantity', 15, 4);
        $table->unsignedBigInteger('sales_order_id')->nullable();
        $table->unsignedBigInteger('project_id')->nullable();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->timestamp('released_at')->nullable();
        $table->timestamp('completed_at')->nullable();
        $table->timestamp('bom_snapshot_at')->nullable();
        $table->decimal('bom_snapshot_planned_quantity', 15, 4)->nullable();
        $table->timestamps();
    });

    Schema::create('production_batches', function (Blueprint $table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('production_order_id');
        $table->string('batch_code', 64);
        $table->text('notes')->nullable();
        $table->timestamp('posted_consumptions_at')->nullable();
        $table->timestamp('posted_receipt_at')->nullable();
        $table->timestamp('completed_at')->nullable();
        $table->timestamps();
    });

    Schema::create('production_batch_consumptions', function (Blueprint $table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('production_batch_id');
        $table->unsignedInteger('component_product_id');
        $table->unsignedBigInteger('warehouse_product_batch_id')->nullable();
        $table->decimal('planned_quantity', 15, 4);
        $table->decimal('planned_quantity_shadow', 20, 6)->nullable();
        $table->json('shadow_basis')->nullable();
        $table->decimal('actual_quantity', 15, 4)->nullable();
        $table->unsignedBigInteger('unit_id')->nullable();
        $table->unsignedInteger('line_order')->default(0);
        $table->timestamps();
    });

    Schema::create('production_batch_outputs', function (Blueprint $table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('production_batch_id');
        $table->unsignedInteger('output_product_id');
        $table->decimal('quantity', 15, 4);
        $table->string('batch_number', 191);
        $table->date('expiration_date')->nullable();
        $table->date('manufacturing_date')->nullable();
        $table->unsignedBigInteger('warehouse_id');
        $table->timestamp('posted_at')->nullable();
        $table->text('variance_reason')->nullable();
        $table->decimal('variance_from_planned_total', 15, 4)->nullable();
        $table->decimal('variance_from_planned_percent', 15, 4)->nullable();
        $table->unsignedBigInteger('approved_by')->nullable();
        $table->timestamp('approved_at')->nullable();
        $table->timestamps();
    });

    Schema::create('production_company_fg_policies', function (Blueprint $table): void {
        $table->id();
        $table->unsignedInteger('company_id')->unique();
        $table->string('policy_mode', 16)->default('controlled');
        $table->decimal('tolerance_percent', 10, 4)->default(5);
        $table->decimal('tolerance_absolute', 15, 4)->default(0);
        $table->boolean('controlled_require_reason_beyond_tolerance')->default(true);
        $table->boolean('controlled_block_beyond_tolerance')->default(false);
        $table->timestamps();
    });

    Schema::create('production_order_bom_snapshot_items', function (Blueprint $table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('production_order_id');
        $table->unsignedInteger('component_product_id');
        $table->decimal('quantity_per_fg_unit', 15, 4);
        $table->decimal('waste_percent', 8, 4)->default(0);
        $table->unsignedBigInteger('unit_id')->nullable();
        $table->decimal('yield_factor', 10, 4)->nullable();
        $table->decimal('quantity_per_fg_unit_base_shadow', 20, 6)->nullable();
        $table->unsignedInteger('sort_order')->default(0);
        $table->timestamps();
    });
}
