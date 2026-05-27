<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Production\Entities\ProductionBatch;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Entities\ProductionBomItem;
use Modules\Production\Entities\ProductionOrder;
use Modules\Production\Services\ProductionOrderMaterialRequirementsSummary;
use Modules\Production\Services\ProductionPostingService;
use Modules\Production\Support\ProductionBatchPlannedLinesPolicy;
use Modules\Production\Support\ProductionBatchWorkflowSteps;
use Modules\Production\Support\ProductionBomFirstPolicy;

beforeEach(function (): void {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Config::set('production.ui.bom_first_workflow_enabled', true);
    Config::set('production.ui.require_bom_on_production_order', true);
    Config::set('production.ui.allow_manual_batch_consumption_lines', false);
    Config::set('production.ui.auto_apply_bom_snapshot_on_batch', true);
    Config::set('production.ui.show_batch_workflow_step_planned_lines', false);
    Config::set('production.ui.show_apply_planned_from_snapshot_button', false);

    Schema::create('companies', function ($table): void {
        $table->increments('id');
        $table->timestamps();
    });

    Schema::create('products', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('name')->nullable();
        $table->string('type')->default('goods');
        $table->unsignedBigInteger('unit_id')->nullable();
        $table->timestamps();
    });

    Schema::create('unit_types', function ($table): void {
        $table->id();
        $table->string('unit_type')->nullable();
        $table->timestamps();
    });

    Schema::create('production_boms', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('output_product_id');
        $table->string('version', 32);
        $table->string('code', 64)->nullable();
        $table->boolean('is_default')->default(false);
        $table->timestamps();
    });

    Schema::create('production_bom_items', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('production_bom_id');
        $table->unsignedBigInteger('component_product_id');
        $table->decimal('quantity', 20, 4);
        $table->decimal('waste_percent', 8, 2)->default(0);
        $table->unsignedBigInteger('unit_id')->nullable();
        $table->integer('sort_order')->default(0);
        $table->timestamps();
    });

    Schema::create('production_orders', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->string('status')->default('draft');
        $table->unsignedBigInteger('output_product_id');
        $table->unsignedBigInteger('production_bom_id')->nullable();
        $table->unsignedBigInteger('rm_warehouse_id')->nullable();
        $table->unsignedBigInteger('fg_warehouse_id')->nullable();
        $table->decimal('planned_quantity', 20, 4);
        $table->timestamps();
    });

    DB::table('companies')->insert(['id' => 1, 'created_at' => now(), 'updated_at' => now()]);
    DB::table('products')->insert([
        ['id' => 10, 'company_id' => 1, 'name' => 'RM', 'type' => 'goods', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 20, 'company_id' => 1, 'name' => 'FG', 'type' => 'goods', 'created_at' => now(), 'updated_at' => now()],
    ]);
});

it('exposes bom first policy flags from config', function (): void {
    expect(ProductionBomFirstPolicy::enabled())->toBeTrue()
        ->and(ProductionBomFirstPolicy::requireBomOnOrder())->toBeTrue()
        ->and(ProductionBomFirstPolicy::allowManualBatchConsumptionLines())->toBeFalse()
        ->and(ProductionBatchPlannedLinesPolicy::autoApplyBomSnapshotOnBatch())->toBeTrue()
        ->and(ProductionBatchPlannedLinesPolicy::showBatchWorkflowStepPlannedLines())->toBeFalse()
        ->and(ProductionBatchPlannedLinesPolicy::showApplyPlannedFromSnapshotButton())->toBeFalse();
});

it('omits planned_lines from batch workflow when step 1 ui is hidden', function (): void {
    Schema::create('production_batches', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('production_order_id');
        $table->string('batch_code');
        $table->timestamp('posted_consumptions_at')->nullable();
        $table->timestamps();
    });

    Schema::create('production_batch_consumptions', function ($table): void {
        $table->id();
        $table->unsignedBigInteger('production_batch_id');
        $table->timestamps();
    });

    Schema::create('production_batch_outputs', function ($table): void {
        $table->id();
        $table->unsignedBigInteger('production_batch_id');
        $table->timestamps();
    });

    $order = ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_RELEASED,
        'output_product_id' => 20,
        'production_bom_id' => null,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 10,
    ]);

    $batch = ProductionBatch::query()->create([
        'company_id' => 1,
        'production_order_id' => $order->id,
        'batch_code' => 'PB-TEST',
    ]);

    $steps = app(ProductionBatchWorkflowSteps::class)->forBatch($batch);
    $keys = collect($steps)->pluck('key');

    expect($keys)->not->toContain('planned_lines')
        ->and($keys)->toHaveCount(4)
        ->and($keys->first())->toBe('assign_batches')
        ->and($steps[0]['display_label'])->toStartWith('1.');
});

it('previews material rows from bom and planned quantity', function (): void {
    $bom = ProductionBom::query()->create([
        'company_id' => 1,
        'output_product_id' => 20,
        'version' => 'v1',
    ]);

    ProductionBomItem::query()->create([
        'company_id' => 1,
        'production_bom_id' => $bom->id,
        'component_product_id' => 10,
        'quantity' => 2,
        'sort_order' => 0,
    ]);

    $rows = app(ProductionOrderMaterialRequirementsSummary::class)->previewForBom(1, (int) $bom->id, 100.0);

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['component_product_id'])->toBe(10)
        ->and($rows[0]['total_required'])->toBe(200.0);
});

it('renders empty bom placeholder on create instead of auto-selecting first bom', function (): void {
    $bomA = ProductionBom::query()->create([
        'company_id' => 1,
        'output_product_id' => 20,
        'version' => 'a',
        'code' => 'bom-a',
    ]);

    ProductionBom::query()->create([
        'company_id' => 1,
        'output_product_id' => 20,
        'version' => 'b',
        'code' => 'bom-b',
    ]);

    $html = view('production::orders.partials.order-bom-header-fields', [
        'finishedGoods' => collect(),
        'boms' => ProductionBom::query()->orderBy('id')->get(),
        'defaultOutputProductId' => null,
        'defaultBomId' => null,
    ])->render();

    expect($html)
        ->toContain(__('production::app.bomSelectPlaceholder'))
        ->toContain('value="" selected')
        ->not->toContain('value="' . $bomA->id . '" selected');
});

it('blocks release without bom when bom first policy is enabled', function (): void {
    $order = ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_DRAFT,
        'output_product_id' => 20,
        'production_bom_id' => null,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 10,
    ]);

    expect(fn() => app(ProductionPostingService::class)->releaseOrder($order))
        ->toThrow(InvalidArgumentException::class);
});
