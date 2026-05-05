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
use Modules\Production\Services\ProductionPostingService;

beforeEach(function (): void {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Config::set('warehouse.allow_negative_stock', false);

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

    $migration = require __DIR__.'/../../Modules/Production/Database/Migrations/2026_05_05_100000_create_production_mvp_tables.php';
    $migration->up();

    DB::table('companies')->insert([
        'company_name' => 'Acme',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('products')->insert([
        ['id' => 1, 'company_id' => 1, 'name' => 'RM', 'type' => 'goods', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'company_id' => 1, 'name' => 'FG', 'type' => 'goods', 'created_at' => now(), 'updated_at' => now()],
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
});

afterEach(function (): void {
    Schema::dropIfExists('production_batch_outputs');
    Schema::dropIfExists('production_batch_consumptions');
    Schema::dropIfExists('production_batches');
    Schema::dropIfExists('production_orders');
    Schema::dropIfExists('production_bom_items');
    Schema::dropIfExists('production_boms');
    Schema::dropIfExists('stock_movements');
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
        ->and($order->fresh()->released_at)->not->toBeNull();

    $service->postConsumptionsForBatch($batch->fresh());

    expect((float) DB::table('warehouse_product_batches')->where('id', 1)->value('quantity'))->toBe(950.0)
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

    expect($fgBatch)->not->toBeNull()
        ->and((float) $fgBatch->quantity)->toBe(100.0)
        ->and($output->fresh()->posted_at)->not->toBeNull()
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
