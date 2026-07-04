<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Production\Entities\ProductionBatch;
use Modules\Production\Entities\ProductionBatchConsumption;
use Modules\Production\Entities\ProductionBatchOutput;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Entities\ProductionBomItem;
use Modules\Production\Entities\ProductionOrder;
use Modules\Production\Entities\ProductionOrderBomSnapshotItem;
use Modules\Production\Services\ProductionPlannedConsumptionFromSnapshotService;
use Modules\Production\Services\ProductionPostingService;
use Modules\Warehouse\Entities\StockReservation;

require_once __DIR__.'/../Support/ProductionPostingSchema.php';

beforeEach(function (): void {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Config::set('warehouse.allow_negative_stock', false);
    Config::set('production.ui.bom_first_workflow_enabled', false);

    Schema::create('companies', function ($table): void {
        $table->increments('id');
        $table->string('company_name')->default('Test Co');
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

    Schema::create('product_unit_conversions', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->unsignedBigInteger('product_id');
        $table->unsignedBigInteger('unit_id');
        $table->decimal('factor_to_base', 20, 6);
        $table->timestamps();
    });

    Schema::create('warehouses', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('name')->nullable();
        $table->string('warehouse_type')->default('normal');
        $table->boolean('is_default')->default(false);
        $table->string('status')->default('active');
        $table->timestamps();
    });

    Schema::create('warehouse_product_batches', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->unsignedBigInteger('warehouse_id');
        $table->unsignedBigInteger('product_id');
        $table->string('batch_number')->nullable();
        $table->date('expiration_date')->nullable();
        $table->date('manufacturing_date')->nullable();
        $table->decimal('quantity', 20, 4)->default(0);
        $table->decimal('reserved_quantity', 20, 4)->default(0);
        $table->timestamps();
    });

    Schema::create('warehouse_product_stock', function ($table): void {
        $table->id();
        $table->unsignedBigInteger('warehouse_id');
        $table->unsignedBigInteger('product_id');
        $table->decimal('quantity', 20, 4)->default(0);
        $table->timestamps();
    });

    Schema::create('stock_reservations', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('warehouse_id');
        $table->unsignedBigInteger('product_id');
        $table->string('batch_number')->nullable();
        $table->date('expiration_date')->nullable();
        $table->decimal('reserved_quantity', 20, 4)->default(0);
        $table->string('reference_type')->nullable();
        $table->unsignedBigInteger('reference_id')->nullable();
        $table->string('status', 20)->default('active');
        $table->timestamps();
    });

    Schema::create('webhooks_settings', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->string('name')->nullable();
        $table->string('webhook_for')->nullable();
        $table->string('status')->default('active');
        $table->timestamps();
    });

    Schema::create('purchase_inventory_adjustment', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->date('date')->nullable();
        $table->string('type')->nullable();
        $table->unsignedBigInteger('warehouse_id')->nullable();
        $table->timestamps();
    });

    Schema::create('purchase_stock_adjustments', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('inventory_id')->nullable();
        $table->unsignedBigInteger('product_id')->nullable();
        $table->unsignedBigInteger('warehouse_id')->nullable();
        $table->string('type')->nullable();
        $table->date('date')->nullable();
        $table->string('batch_number')->nullable();
        $table->string('reference_number')->nullable();
        $table->text('description')->nullable();
        $table->decimal('net_quantity', 20, 4)->nullable();
        $table->string('status')->nullable();
        $table->timestamps();
    });

    Schema::create('stock_movements', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('product_id')->nullable();
        $table->unsignedBigInteger('delivery_order_item_id')->nullable();
        $table->string('movement_type');
        $table->unsignedBigInteger('warehouse_from_id')->nullable();
        $table->unsignedBigInteger('warehouse_to_id')->nullable();
        $table->string('batch_number')->nullable();
        $table->date('expiry_date')->nullable();
        $table->decimal('quantity', 20, 4)->default(0);
        $table->string('fefo_fifo_rule')->nullable();
        $table->string('reference_type')->nullable();
        $table->unsignedBigInteger('reference_id')->nullable();
        $table->string('idempotency_key', 120)->nullable();
        $table->timestamps();
    });

    createProductionPostingSchema();

    DB::table('companies')->insert([
        'company_name' => 'Acme',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('products')->insert([
        ['id' => 1, 'company_id' => 1, 'name' => 'RM', 'type' => 'raw_material', 'unit_id' => 99, 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'company_id' => 1, 'name' => 'FG', 'type' => 'goods', 'unit_id' => null, 'created_at' => now(), 'updated_at' => now()],
    ]);

    DB::table('unit_types')->insert([
        ['id' => 10, 'unit_type' => 'kg', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 99, 'unit_type' => 'g', 'created_at' => now(), 'updated_at' => now()],
    ]);

    DB::table('warehouses')->insert([
        'company_id' => 1,
        'name' => 'Main',
        'warehouse_type' => 'normal',
        'is_default' => true,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('warehouse_product_batches')->insert([
        'company_id' => 1,
        'warehouse_id' => 1,
        'product_id' => 1,
        'batch_number' => 'RM-01',
        'expiration_date' => null,
        'manufacturing_date' => null,
        'quantity' => 1000,
        'reserved_quantity' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('warehouse_product_stock')->insert([
        'warehouse_id' => 1,
        'product_id' => 1,
        'quantity' => 1000,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

afterEach(function (): void {
    Schema::dropIfExists('production_company_fg_policies');
    Schema::dropIfExists('production_batch_outputs');
    Schema::dropIfExists('production_batch_consumptions');
    Schema::dropIfExists('production_batches');
    Schema::dropIfExists('production_order_bom_snapshot_items');
    Schema::dropIfExists('production_orders');
    Schema::dropIfExists('production_bom_items');
    Schema::dropIfExists('production_boms');
    Schema::dropIfExists('webhooks_settings');
    Schema::dropIfExists('purchase_stock_adjustments');
    Schema::dropIfExists('purchase_inventory_adjustment');
    Schema::dropIfExists('stock_movements');
    Schema::dropIfExists('stock_reservations');
    Schema::dropIfExists('product_unit_conversions');
    Schema::dropIfExists('unit_types');
    Schema::dropIfExists('warehouse_product_stock');
    Schema::dropIfExists('warehouse_product_batches');
    Schema::dropIfExists('warehouses');
    Schema::dropIfExists('products');
    Schema::dropIfExists('companies');
});

it('posts RM consumption then FG receipt via warehouse stock movements', function (): void {
    $bom = ProductionBom::query()->create([
        'company_id' => 1,
        'output_product_id' => 2,
        'version' => 'v1',
        'code' => 'BOM-FG2',
        'is_default' => true,
    ]);

    ProductionBomItem::query()->create([
        'company_id' => 1,
        'production_bom_id' => $bom->id,
        'component_product_id' => 1,
        'quantity' => 0.5,
        'sort_order' => 0,
    ]);

    $order = ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_DRAFT,
        'output_product_id' => 2,
        'production_bom_id' => $bom->id,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 100,
    ]);

    $batch = ProductionBatch::query()->create([
        'company_id' => 1,
        'production_order_id' => $order->id,
        'batch_code' => 'PB-001',
    ]);

    ProductionBatchConsumption::query()->create([
        'company_id' => 1,
        'production_batch_id' => $batch->id,
        'component_product_id' => 1,
        'warehouse_product_batch_id' => 1,
        'planned_quantity' => 50,
        'actual_quantity' => null,
        'line_order' => 0,
    ]);

    $service = app(ProductionPostingService::class);

    $service->releaseOrder($order);
    expect($order->fresh()->status)->toBe(ProductionOrder::STATUS_RELEASED)
        ->and($order->fresh()->released_at)->not->toBeNull()
        ->and(ProductionOrderBomSnapshotItem::query()->where('production_order_id', $order->id)->count())->toBe(1)
        ->and((float) ProductionOrderBomSnapshotItem::query()->where('production_order_id', $order->id)->value('quantity_per_fg_unit'))->toBe(0.5)
        ->and($order->fresh()->bom_snapshot_planned_quantity)->toBe(100.0)
        ->and((float) DB::table('warehouse_product_batches')->where('id', 1)->value('reserved_quantity'))->toBe(50.0)
        ->and(
            StockReservation::query()
                ->where('reference_type', ProductionOrder::class)
                ->where('reference_id', $order->id)
                ->where('status', 'active')
                ->count()
        )->toBe(1);

    $service->postConsumptionsForBatch($batch->fresh());

    expect((float) DB::table('warehouse_product_batches')->where('id', 1)->value('quantity'))->toBe(950.0)
        ->and((float) DB::table('warehouse_product_batches')->where('id', 1)->value('reserved_quantity'))->toBe(0.0)
        ->and(
            StockReservation::query()
                ->where('reference_type', ProductionOrder::class)
                ->where('reference_id', $order->id)
                ->where('status', 'consumed')
                ->count()
        )->toBe(1)
        ->and($batch->fresh()->posted_consumptions_at)->not->toBeNull()
        ->and($order->fresh()->status)->toBe(ProductionOrder::STATUS_IN_PROGRESS);

    $output = ProductionBatchOutput::query()->create([
        'company_id' => 1,
        'production_batch_id' => $batch->id,
        'output_product_id' => 2,
        'quantity' => 100,
        'batch_number' => 'FG-LOT-1',
        'expiration_date' => null,
        'manufacturing_date' => null,
        'warehouse_id' => 1,
    ]);

    $service->postFinishedGoodsReceipt($output->fresh());

    $fgBatch = DB::table('warehouse_product_batches')
        ->where('warehouse_id', 1)
        ->where('product_id', 2)
        ->where('batch_number', 'FG-LOT-1')
        ->first();

    $ledgerLine = DB::table('purchase_stock_adjustments')
        ->where('company_id', 1)
        ->where('product_id', 2)
        ->where('warehouse_id', 1)
        ->first();

    expect($fgBatch)->not->toBeNull()
        ->and((float) $fgBatch->quantity)->toBe(100.0)
        ->and($output->fresh()->posted_at)->not->toBeNull()
        ->and($order->fresh()->status)->toBe(ProductionOrder::STATUS_COMPLETED)
        ->and($order->fresh()->completed_at)->not->toBeNull()
        ->and($ledgerLine)->not->toBeNull()
        ->and((float) $ledgerLine->net_quantity)->toBe(100.0)
        ->and($ledgerLine->batch_number)->toBe('FG-LOT-1');
});

it('consumes raw material fully reserved by the same production order', function (): void {
    DB::table('warehouse_product_batches')->where('id', 1)->update([
        'quantity' => 50,
        'reserved_quantity' => 0,
    ]);
    DB::table('warehouse_product_stock')->where('warehouse_id', 1)->where('product_id', 1)->update([
        'quantity' => 50,
    ]);

    $bom = ProductionBom::query()->create([
        'company_id' => 1,
        'output_product_id' => 2,
        'version' => 'v-exact-reserve',
        'code' => 'BOM-EXACT-RESERVE',
        'is_default' => true,
    ]);
    ProductionBomItem::query()->create([
        'company_id' => 1,
        'production_bom_id' => $bom->id,
        'component_product_id' => 1,
        'quantity' => 1,
        'sort_order' => 0,
    ]);
    $order = ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_DRAFT,
        'output_product_id' => 2,
        'production_bom_id' => $bom->id,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 50,
    ]);
    $batch = ProductionBatch::query()->create([
        'company_id' => 1,
        'production_order_id' => $order->id,
        'batch_code' => 'PB-EXACT-RESERVE',
    ]);
    ProductionBatchConsumption::query()->create([
        'company_id' => 1,
        'production_batch_id' => $batch->id,
        'component_product_id' => 1,
        'warehouse_product_batch_id' => 1,
        'planned_quantity' => 50,
        'line_order' => 0,
    ]);

    $service = app(ProductionPostingService::class);
    $service->releaseOrder($order);
    expect((float) DB::table('warehouse_product_batches')->where('id', 1)->value('reserved_quantity'))->toBe(50.0);

    $service->postConsumptionsForBatch($batch->fresh());

    expect((float) DB::table('warehouse_product_batches')->where('id', 1)->value('quantity'))->toBe(0.0)
        ->and((float) DB::table('warehouse_product_batches')->where('id', 1)->value('reserved_quantity'))->toBe(0.0)
        ->and(StockReservation::query()
            ->where('reference_type', ProductionOrder::class)
            ->where('reference_id', $order->id)
            ->where('status', 'consumed')
            ->count())->toBe(1);
});

it('posts RM consumption in product base unit when line unit_id differs from base (P2-UOM-OUTBOUND)', function (): void {
    DB::table('products')->where('id', 1)->update([
        'unit_id' => 99,
        'updated_at' => now(),
    ]);

    DB::table('product_unit_conversions')->insert([
        'company_id' => 1,
        'product_id' => 1,
        'unit_id' => 10,
        'factor_to_base' => 0.001,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('warehouse_product_batches')->where('id', 1)->update([
        'quantity' => 1000,
        'updated_at' => now(),
    ]);

    $order = ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_DRAFT,
        'output_product_id' => 2,
        'production_bom_id' => null,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 10,
    ]);

    $batch = ProductionBatch::query()->create([
        'company_id' => 1,
        'production_order_id' => $order->id,
        'batch_code' => 'PB-UOM-OUT',
    ]);

    ProductionBatchConsumption::query()->create([
        'company_id' => 1,
        'production_batch_id' => $batch->id,
        'component_product_id' => 1,
        'warehouse_product_batch_id' => 1,
        'planned_quantity' => 100,
        'unit_id' => 10,
        'actual_quantity' => null,
        'line_order' => 0,
    ]);

    $service = app(ProductionPostingService::class);
    $service->releaseOrder($order);
    $service->postConsumptionsForBatch($batch->fresh());

    expect((float) DB::table('warehouse_product_batches')->where('id', 1)->value('quantity'))->toBe(999.9)
        ->and($batch->fresh()->posted_consumptions_at)->not->toBeNull();
});

it('defers batch receipt and order completion until every output line on the batch is posted', function (): void {
    $bom = ProductionBom::query()->create([
        'company_id' => 1,
        'output_product_id' => 2,
        'version' => 'v1',
        'code' => 'BOM-FG2-SPLIT',
        'is_default' => true,
    ]);

    ProductionBomItem::query()->create([
        'company_id' => 1,
        'production_bom_id' => $bom->id,
        'component_product_id' => 1,
        'quantity' => 0.5,
        'sort_order' => 0,
    ]);

    $order = ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_DRAFT,
        'output_product_id' => 2,
        'production_bom_id' => $bom->id,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 100,
    ]);

    $batch = ProductionBatch::query()->create([
        'company_id' => 1,
        'production_order_id' => $order->id,
        'batch_code' => 'PB-SPLIT-FG',
    ]);

    ProductionBatchConsumption::query()->create([
        'company_id' => 1,
        'production_batch_id' => $batch->id,
        'component_product_id' => 1,
        'warehouse_product_batch_id' => 1,
        'planned_quantity' => 50,
        'actual_quantity' => null,
        'line_order' => 0,
    ]);

    $service = app(ProductionPostingService::class);
    $service->releaseOrder($order);
    $service->postConsumptionsForBatch($batch->fresh());

    $outputA = ProductionBatchOutput::query()->create([
        'company_id' => 1,
        'production_batch_id' => $batch->id,
        'output_product_id' => 2,
        'quantity' => 60,
        'batch_number' => 'FG-SPLIT-A',
        'expiration_date' => null,
        'manufacturing_date' => null,
        'warehouse_id' => 1,
    ]);

    $outputB = ProductionBatchOutput::query()->create([
        'company_id' => 1,
        'production_batch_id' => $batch->id,
        'output_product_id' => 2,
        'quantity' => 40,
        'batch_number' => 'FG-SPLIT-B',
        'expiration_date' => null,
        'manufacturing_date' => null,
        'warehouse_id' => 1,
    ]);

    $service->postFinishedGoodsReceipt($outputA->fresh());

    expect($batch->fresh()->posted_receipt_at)->toBeNull()
        ->and($batch->fresh()->completed_at)->toBeNull()
        ->and($order->fresh()->status)->toBe(ProductionOrder::STATUS_IN_PROGRESS)
        ->and($order->fresh()->completed_at)->toBeNull();

    $service->postFinishedGoodsReceipt($outputB->fresh());

    expect($batch->fresh()->posted_receipt_at)->not->toBeNull()
        ->and($batch->fresh()->completed_at)->not->toBeNull()
        ->and($order->fresh()->status)->toBe(ProductionOrder::STATUS_COMPLETED)
        ->and($order->fresh()->completed_at)->not->toBeNull();
});

it('throws when posting consumptions with no consumption lines', function (): void {
    $order = ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_RELEASED,
        'output_product_id' => 2,
        'production_bom_id' => null,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 10,
    ]);

    $batch = ProductionBatch::query()->create([
        'company_id' => 1,
        'production_order_id' => $order->id,
        'batch_code' => 'PB-EMPTY',
    ]);

    $service = app(ProductionPostingService::class);

    $service->postConsumptionsForBatch($batch->fresh());
})->throws(InvalidArgumentException::class);

it('skips posting consumptions again when batch already posted', function (): void {
    $bom = ProductionBom::query()->create([
        'company_id' => 1,
        'output_product_id' => 2,
        'version' => 'v1',
        'code' => 'BOM-FG2',
        'is_default' => true,
    ]);

    ProductionBomItem::query()->create([
        'company_id' => 1,
        'production_bom_id' => $bom->id,
        'component_product_id' => 1,
        'quantity' => 0.5,
        'sort_order' => 0,
    ]);

    $order = ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_DRAFT,
        'output_product_id' => 2,
        'production_bom_id' => $bom->id,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 100,
    ]);

    $batch = ProductionBatch::query()->create([
        'company_id' => 1,
        'production_order_id' => $order->id,
        'batch_code' => 'PB-002',
    ]);

    ProductionBatchConsumption::query()->create([
        'company_id' => 1,
        'production_batch_id' => $batch->id,
        'component_product_id' => 1,
        'warehouse_product_batch_id' => 1,
        'planned_quantity' => 10,
        'actual_quantity' => null,
        'line_order' => 0,
    ]);

    $service = app(ProductionPostingService::class);
    $service->releaseOrder($order);
    $service->postConsumptionsForBatch($batch->fresh());

    $qtyAfterFirst = (float) DB::table('warehouse_product_batches')->where('id', 1)->value('quantity');

    $service->postConsumptionsForBatch($batch->fresh());

    expect((float) DB::table('warehouse_product_batches')->where('id', 1)->value('quantity'))->toBe($qtyAfterFirst);
});

it('cancels a draft production order without touching stock', function (): void {
    $bom = ProductionBom::query()->create([
        'company_id' => 1,
        'output_product_id' => 2,
        'version' => 'v1',
        'code' => 'BOM-FG2',
        'is_default' => true,
    ]);

    $order = ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_DRAFT,
        'output_product_id' => 2,
        'production_bom_id' => $bom->id,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 10,
    ]);

    $service = app(ProductionPostingService::class);
    $service->cancelOrder($order);

    expect($order->fresh()->status)->toBe(ProductionOrder::STATUS_CANCELLED)
        ->and((float) DB::table('warehouse_product_batches')->where('id', 1)->value('quantity'))->toBe(1000.0);
});

it('cancels a released order when no stock movements were posted', function (): void {
    $bom = ProductionBom::query()->create([
        'company_id' => 1,
        'output_product_id' => 2,
        'version' => 'v1',
        'code' => 'BOM-FG2',
        'is_default' => true,
    ]);

    $order = ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_DRAFT,
        'output_product_id' => 2,
        'production_bom_id' => $bom->id,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 10,
    ]);

    ProductionBatch::query()->create([
        'company_id' => 1,
        'production_order_id' => $order->id,
        'batch_code' => 'PB-CANCEL-REL',
    ]);

    $service = app(ProductionPostingService::class);
    $service->releaseOrder($order);
    $service->cancelOrder($order->fresh());

    expect($order->fresh()->status)->toBe(ProductionOrder::STATUS_CANCELLED);
});

it('keeps order in progress when other batches are not completed yet', function (): void {
    $bom = ProductionBom::query()->create([
        'company_id' => 1,
        'output_product_id' => 2,
        'version' => 'v1',
        'code' => 'BOM-FG2',
        'is_default' => true,
    ]);

    $order = ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_DRAFT,
        'output_product_id' => 2,
        'production_bom_id' => $bom->id,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 100,
    ]);

    $batchA = ProductionBatch::query()->create([
        'company_id' => 1,
        'production_order_id' => $order->id,
        'batch_code' => 'PB-A',
        'posted_consumptions_at' => now(),
    ]);

    ProductionBatch::query()->create([
        'company_id' => 1,
        'production_order_id' => $order->id,
        'batch_code' => 'PB-B',
    ]);

    $output = ProductionBatchOutput::query()->create([
        'company_id' => 1,
        'production_batch_id' => $batchA->id,
        'output_product_id' => 2,
        'quantity' => 40,
        'batch_number' => 'FG-LOT-A',
        'expiration_date' => null,
        'manufacturing_date' => null,
        'warehouse_id' => 1,
    ]);

    $service = app(ProductionPostingService::class);
    $service->releaseOrder($order);
    $service->postFinishedGoodsReceipt($output->fresh());

    expect($order->fresh()->status)->toBe(ProductionOrder::STATUS_IN_PROGRESS)
        ->and($order->fresh()->completed_at)->toBeNull();
});

it('does not create BOM snapshot when releasing without BOM', function (): void {
    $order = ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_DRAFT,
        'output_product_id' => 2,
        'production_bom_id' => null,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 10,
    ]);

    $service = app(ProductionPostingService::class);
    $service->releaseOrder($order);

    expect(ProductionOrderBomSnapshotItem::query()->where('production_order_id', $order->id)->count())->toBe(0)
        ->and($order->fresh()->bom_snapshot_at)->toBeNull();
});

it('creates planned consumption lines from snapshot for a single-batch order', function (): void {
    $bom = ProductionBom::query()->create([
        'company_id' => 1,
        'output_product_id' => 2,
        'version' => 'v1',
        'code' => 'BOM-FG2',
        'is_default' => true,
    ]);

    ProductionBomItem::query()->create([
        'company_id' => 1,
        'production_bom_id' => $bom->id,
        'component_product_id' => 1,
        'quantity' => 0.5,
        'sort_order' => 0,
    ]);

    $order = ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_DRAFT,
        'output_product_id' => 2,
        'production_bom_id' => $bom->id,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 100,
    ]);

    $batch = ProductionBatch::query()->create([
        'company_id' => 1,
        'production_order_id' => $order->id,
        'batch_code' => 'PB-SNAPSHOT',
    ]);

    $service = app(ProductionPostingService::class);
    $service->releaseOrder($order);

    $apply = app(ProductionPlannedConsumptionFromSnapshotService::class);
    $apply->applySnapshotToBatch($batch->fresh(['order']));

    $consumption = ProductionBatchConsumption::query()->where('production_batch_id', $batch->id)->first();

    expect(ProductionBatchConsumption::query()->where('production_batch_id', $batch->id)->count())->toBe(1)
        ->and((float) $consumption->planned_quantity)->toBe(50.0)
        ->and($consumption->planned_quantity_shadow)->toBeNull()
        ->and($consumption->warehouse_product_batch_id)->toBeNull();

    $order->refresh();
    $snapshot = ProductionOrderBomSnapshotItem::query()->where('production_order_id', $order->id)->firstOrFail();
    expect($snapshot->quantity_per_fg_unit_base_shadow)->toBeNull();
});

it('computes planned_quantity_shadow using UOM conversion and yield factor in shadow mode', function (): void {
    Config::set('production.phase2.yield_uom_shadow_enabled', true);

    DB::table('products')->where('id', 1)->update([
        'unit_id' => 99,
        'updated_at' => now(),
    ]);

    DB::table('product_unit_conversions')->insert([
        'company_id' => 1,
        'product_id' => 1,
        'unit_id' => 10,
        'factor_to_base' => 2.5,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $bom = ProductionBom::query()->create([
        'company_id' => 1,
        'output_product_id' => 2,
        'version' => 'v-uom-yield',
        'code' => 'BOM-UOM-YIELD',
        'is_default' => true,
    ]);

    ProductionBomItem::query()->create([
        'company_id' => 1,
        'production_bom_id' => $bom->id,
        'component_product_id' => 1,
        'quantity' => 4.0,
        'unit_id' => 10,
        'yield_factor' => 0.8,
        'sort_order' => 0,
    ]);

    $order = ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_DRAFT,
        'output_product_id' => 2,
        'production_bom_id' => $bom->id,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 10,
    ]);

    $batch = ProductionBatch::query()->create([
        'company_id' => 1,
        'production_order_id' => $order->id,
        'batch_code' => 'PB-UOM-YIELD',
    ]);

    $posting = app(ProductionPostingService::class);
    $posting->releaseOrder($order);

    $apply = app(ProductionPlannedConsumptionFromSnapshotService::class);
    $apply->applySnapshotToBatch($batch->fresh(['order']));

    $consumption = ProductionBatchConsumption::query()->where('production_batch_id', $batch->id)->firstOrFail();

    expect((float) $consumption->planned_quantity)->toBe(40.0)
        ->and((float) $consumption->planned_quantity_shadow)->toBe(125.0)
        ->and((float) data_get($consumption->shadow_basis, 'quantity_per_fg_unit_base'))->toBe(12.5)
        ->and((float) data_get($consumption->shadow_basis, 'yield_factor'))->toBe(0.8);
});

it('creates planned consumption from snapshot for each batch with equal FG split across batches', function (): void {
    $bom = ProductionBom::query()->create([
        'company_id' => 1,
        'output_product_id' => 2,
        'version' => 'v1',
        'code' => 'BOM-FG2',
        'is_default' => true,
    ]);

    ProductionBomItem::query()->create([
        'company_id' => 1,
        'production_bom_id' => $bom->id,
        'component_product_id' => 1,
        'quantity' => 0.5,
        'sort_order' => 0,
    ]);

    $order = ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_DRAFT,
        'output_product_id' => 2,
        'production_bom_id' => $bom->id,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 100,
    ]);

    $batchA = ProductionBatch::query()->create([
        'company_id' => 1,
        'production_order_id' => $order->id,
        'batch_code' => 'PB-A',
    ]);

    $batchB = ProductionBatch::query()->create([
        'company_id' => 1,
        'production_order_id' => $order->id,
        'batch_code' => 'PB-B',
    ]);

    $service = app(ProductionPostingService::class);
    $service->releaseOrder($order);

    $apply = app(ProductionPlannedConsumptionFromSnapshotService::class);
    $apply->applySnapshotToBatch($batchA->fresh(['order']));
    $apply->applySnapshotToBatch($batchB->fresh(['order']));

    $qtyA = (float) ProductionBatchConsumption::query()->where('production_batch_id', $batchA->id)->value('planned_quantity');
    $qtyB = (float) ProductionBatchConsumption::query()->where('production_batch_id', $batchB->id)->value('planned_quantity');

    expect($qtyA)->toBe(25.0)
        ->and($qtyB)->toBe(25.0)
        ->and(ProductionBatchConsumption::query()->whereIn('production_batch_id', [$batchA->id, $batchB->id])->count())->toBe(2);
});

it('auto-assigns RM warehouse batch when consumption line has no assigned batch', function (): void {
    $order = ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_RELEASED,
        'output_product_id' => 2,
        'production_bom_id' => null,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 10,
    ]);

    $batch = ProductionBatch::query()->create([
        'company_id' => 1,
        'production_order_id' => $order->id,
        'batch_code' => 'PB-AUTO-RM-BATCH',
    ]);

    $consumption = ProductionBatchConsumption::query()->create([
        'company_id' => 1,
        'production_batch_id' => $batch->id,
        'component_product_id' => 1,
        'warehouse_product_batch_id' => null,
        'planned_quantity' => 25,
        'actual_quantity' => null,
        'line_order' => 0,
    ]);

    $service = app(ProductionPostingService::class);
    $service->postConsumptionsForBatch($batch->fresh());

    $consumption->refresh();
    expect($consumption->warehouse_product_batch_id)->not->toBeNull()
        ->and((float) DB::table('warehouse_product_batches')->where('id', 1)->value('quantity'))->toBe(975.0)
        ->and($batch->fresh()->posted_consumptions_at)->not->toBeNull();
});

it('falls back to another RM batch when selected batch lacks quantity', function (): void {
    DB::table('warehouse_product_batches')->where('id', 1)->update([
        'quantity' => 6,
        'updated_at' => now(),
    ]);

    $otherBatchId = DB::table('warehouse_product_batches')->insertGetId([
        'company_id' => 1,
        'warehouse_id' => 1,
        'product_id' => 1,
        'batch_number' => 'RM-02',
        'expiration_date' => null,
        'manufacturing_date' => null,
        'quantity' => 194,
        'reserved_quantity' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $order = ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_RELEASED,
        'output_product_id' => 2,
        'production_bom_id' => null,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 10,
    ]);

    $batch = ProductionBatch::query()->create([
        'company_id' => 1,
        'production_order_id' => $order->id,
        'batch_code' => 'PB-FALLBACK-RM-BATCH',
    ]);

    $consumption = ProductionBatchConsumption::query()->create([
        'company_id' => 1,
        'production_batch_id' => $batch->id,
        'component_product_id' => 1,
        'warehouse_product_batch_id' => 1,
        'planned_quantity' => 30,
        'actual_quantity' => null,
        'line_order' => 0,
    ]);

    $service = app(ProductionPostingService::class);
    $service->postConsumptionsForBatch($batch->fresh());

    $consumption->refresh();
    expect($consumption->warehouse_product_batch_id)->toBe(1)
        ->and((float) DB::table('warehouse_product_batches')->where('id', 1)->value('quantity'))->toBe(0.0)
        ->and((float) DB::table('warehouse_product_batches')->where('id', $otherBatchId)->value('quantity'))->toBe(170.0)
        ->and($batch->fresh()->posted_consumptions_at)->not->toBeNull();
});

it('splits RM consumption across multiple warehouse batches when needed', function (): void {
    DB::table('warehouse_product_batches')->where('id', 1)->update([
        'quantity' => 6,
        'updated_at' => now(),
    ]);

    $otherBatchId = DB::table('warehouse_product_batches')->insertGetId([
        'company_id' => 1,
        'warehouse_id' => 1,
        'product_id' => 1,
        'batch_number' => 'RM-03',
        'expiration_date' => null,
        'manufacturing_date' => null,
        'quantity' => 20,
        'reserved_quantity' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $order = ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_RELEASED,
        'output_product_id' => 2,
        'production_bom_id' => null,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 10,
    ]);

    $batch = ProductionBatch::query()->create([
        'company_id' => 1,
        'production_order_id' => $order->id,
        'batch_code' => 'PB-SPLIT-RM-BATCH',
    ]);

    $consumption = ProductionBatchConsumption::query()->create([
        'company_id' => 1,
        'production_batch_id' => $batch->id,
        'component_product_id' => 1,
        'warehouse_product_batch_id' => 1,
        'planned_quantity' => 25,
        'actual_quantity' => null,
        'line_order' => 0,
    ]);

    $service = app(ProductionPostingService::class);
    $service->postConsumptionsForBatch($batch->fresh());

    $consumption->refresh();
    expect($consumption->warehouse_product_batch_id)->toBe(1)
        ->and((float) DB::table('warehouse_product_batches')->where('id', 1)->value('quantity'))->toBe(0.0)
        ->and((float) DB::table('warehouse_product_batches')->where('id', $otherBatchId)->value('quantity'))->toBe(1.0)
        ->and($batch->fresh()->posted_consumptions_at)->not->toBeNull();

    $movementCount = DB::table('stock_movements')
        ->where('reference_type', ProductionBatch::class)
        ->where('reference_id', $batch->id)
        ->where('movement_type', 'outbound')
        ->count();

    expect($movementCount)->toBe(2);
});

it('requires variance approval before posting FG receipt when phase2 flag is enabled', function (): void {
    Config::set('production.phase2.enforce_variance_approval', true);

    $order = ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_RELEASED,
        'output_product_id' => 2,
        'production_bom_id' => null,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 100,
    ]);

    $batch = ProductionBatch::query()->create([
        'company_id' => 1,
        'production_order_id' => $order->id,
        'batch_code' => 'PB-APPROVAL',
        'posted_consumptions_at' => now(),
    ]);

    $service = app(ProductionPostingService::class);

    $output = ProductionBatchOutput::query()->create([
        'company_id' => 1,
        'production_batch_id' => $batch->id,
        'output_product_id' => 2,
        'quantity' => 120,
        'batch_number' => 'FG-APPROVAL',
        'warehouse_id' => 1,
        'variance_reason' => 'Pilot overrun',
        'variance_from_planned_total' => 20,
        'variance_from_planned_percent' => 20,
    ]);

    $service->postFinishedGoodsReceipt($output->fresh());
})->throws(InvalidArgumentException::class);

it('posts FG receipt after variance is approved', function (): void {
    Config::set('production.phase2.enforce_variance_approval', true);

    $order = ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_RELEASED,
        'output_product_id' => 2,
        'production_bom_id' => null,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 100,
    ]);

    $batch = ProductionBatch::query()->create([
        'company_id' => 1,
        'production_order_id' => $order->id,
        'batch_code' => 'PB-APPROVED',
        'posted_consumptions_at' => now(),
    ]);

    $output = ProductionBatchOutput::query()->create([
        'company_id' => 1,
        'production_batch_id' => $batch->id,
        'output_product_id' => 2,
        'quantity' => 120,
        'batch_number' => 'FG-APPROVED',
        'warehouse_id' => 1,
        'variance_reason' => 'Pilot overrun',
        'variance_from_planned_total' => 20,
        'variance_from_planned_percent' => 20,
        'approved_by' => 99,
        'approved_at' => now(),
    ]);

    $service = app(ProductionPostingService::class);
    $service->postFinishedGoodsReceipt($output->fresh());

    expect($output->fresh()->posted_at)->not->toBeNull()
        ->and($order->fresh()->status)->toBe(ProductionOrder::STATUS_COMPLETED);
});

it('does not reserve raw materials while production order is draft', function (): void {
    $bom = ProductionBom::query()->create([
        'company_id' => 1,
        'output_product_id' => 2,
        'version' => 'v-res-draft',
        'code' => 'BOM-RES-DRAFT',
        'is_default' => true,
    ]);

    ProductionBomItem::query()->create([
        'company_id' => 1,
        'production_bom_id' => $bom->id,
        'component_product_id' => 1,
        'quantity' => 1,
        'sort_order' => 0,
    ]);

    $order = ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_DRAFT,
        'output_product_id' => 2,
        'production_bom_id' => $bom->id,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 10,
    ]);

    expect(StockReservation::query()->where('reference_id', $order->id)->count())->toBe(0);
});

it('blocks release when insufficient available raw material to reserve', function (): void {
    $bom = ProductionBom::query()->create([
        'company_id' => 1,
        'output_product_id' => 2,
        'version' => 'v-res-block',
        'code' => 'BOM-RES-BLOCK',
        'is_default' => true,
    ]);

    ProductionBomItem::query()->create([
        'company_id' => 1,
        'production_bom_id' => $bom->id,
        'component_product_id' => 1,
        'quantity' => 1,
        'sort_order' => 0,
    ]);

    DB::table('warehouse_product_batches')->where('id', 1)->update(['quantity' => 5]);
    DB::table('warehouse_product_stock')->where('product_id', 1)->update(['quantity' => 5]);

    $order = ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_DRAFT,
        'output_product_id' => 2,
        'production_bom_id' => $bom->id,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 10,
    ]);

    expect(fn () => app(ProductionPostingService::class)->releaseOrder($order))
        ->toThrow(InvalidArgumentException::class);

    expect($order->fresh()->status)->toBe(ProductionOrder::STATUS_DRAFT);
});

it('releases raw material reservations when a released production order is cancelled', function (): void {
    $bom = ProductionBom::query()->create([
        'company_id' => 1,
        'output_product_id' => 2,
        'version' => 'v-res-cancel',
        'code' => 'BOM-RES-CANCEL',
        'is_default' => true,
    ]);

    ProductionBomItem::query()->create([
        'company_id' => 1,
        'production_bom_id' => $bom->id,
        'component_product_id' => 1,
        'quantity' => 1,
        'sort_order' => 0,
    ]);

    $order = ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_DRAFT,
        'output_product_id' => 2,
        'production_bom_id' => $bom->id,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 10,
    ]);

    $posting = app(ProductionPostingService::class);
    $posting->releaseOrder($order);

    ProductionBatch::query()->create([
        'company_id' => 1,
        'production_order_id' => $order->id,
        'batch_code' => 'PB-CANCEL-RES',
    ]);

    $posting->cancelOrder($order->fresh());

    expect($order->fresh()->status)->toBe(ProductionOrder::STATUS_CANCELLED)
        ->and((float) DB::table('warehouse_product_batches')->where('id', 1)->value('reserved_quantity'))->toBe(0.0)
        ->and(
            StockReservation::query()
                ->where('reference_type', ProductionOrder::class)
                ->where('reference_id', $order->id)
                ->where('status', 'active')
                ->count()
        )->toBe(0);
});
