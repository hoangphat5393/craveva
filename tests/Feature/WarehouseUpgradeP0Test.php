<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Entities\PurchaseOrder;
use Modules\Purchase\Entities\SalesDo;
use Modules\Purchase\Observers\PurchaseOrderObserver;
use Modules\Purchase\Services\SalesDoService;
use Modules\Warehouse\Exceptions\WarehouseBusinessException;
use Modules\Warehouse\Services\InvoiceWarehouseStockService;
use Modules\Warehouse\Services\StockMovementService;
use Modules\Warehouse\Services\WarehouseAvailabilityService;

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    session()->start();
    session(['user' => (object) ['id' => 1]]);

    Config::set('warehouse.inbound_from_purchase_order_delivered', true);
    Config::set('warehouse.inbound_from_delivery_order_received', false);
    Config::set('warehouse.sales_outbound_enabled', true);
    Config::set('warehouse.sales_outbound_mode', 'shipment');

    Schema::create('products', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->string('name')->nullable();
        $table->string('sku')->nullable();
        $table->string('type')->default('goods');
        $table->timestamps();
    });

    Schema::create('warehouses', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->string('name')->nullable();
        $table->string('code')->nullable();
        $table->string('warehouse_type')->default('normal');
        $table->boolean('is_default')->default(false);
        $table->enum('status', ['active', 'inactive'])->default('active');
        $table->timestamps();
    });

    Schema::create('warehouse_product_batches', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('warehouse_id');
        $table->unsignedBigInteger('product_id');
        $table->string('batch_number')->nullable();
        $table->date('expiration_date')->nullable();
        $table->decimal('quantity', 20, 4)->default(0);
        $table->decimal('reserved_quantity', 20, 4)->default(0);
        $table->timestamps();
    });

    Schema::create('warehouse_product_stock', function ($table) {
        $table->id();
        $table->unsignedBigInteger('warehouse_id');
        $table->unsignedBigInteger('product_id');
        $table->decimal('quantity', 20, 4)->default(0);
        $table->timestamps();
    });

    Schema::create('stock_reservations', function ($table) {
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

    Schema::create('stock_movements', function ($table) {
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
        $table->timestamps();
    });

    Schema::create('sales_dos', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('order_id')->nullable();
        $table->unsignedBigInteger('warehouse_id')->nullable();
        $table->string('do_number')->nullable();
        $table->date('do_date')->nullable();
        $table->string('status')->default('draft');
        $table->boolean('outbound_stock_applied')->default(false);
        $table->text('notes')->nullable();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->timestamps();
    });

    Schema::create('sales_do_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('sales_do_id');
        $table->unsignedBigInteger('order_item_id')->nullable();
        $table->unsignedBigInteger('product_id')->nullable();
        $table->decimal('quantity_ordered', 20, 4)->default(0);
        $table->decimal('quantity_shipped', 20, 4)->default(0);
        $table->unsignedBigInteger('unit_id')->nullable();
        $table->string('batch_number')->nullable();
        $table->timestamps();
    });

    DB::table('products')->insert([
        'id' => 100,
        'company_id' => 1,
        'name' => 'SKU-100',
        'sku' => 'SKU100',
        'type' => 'goods',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

afterEach(function () {
    Schema::dropIfExists('sales_do_items');
    Schema::dropIfExists('sales_dos');
    Schema::dropIfExists('stock_movements');
    Schema::dropIfExists('stock_reservations');
    Schema::dropIfExists('warehouse_product_stock');
    Schema::dropIfExists('warehouse_product_batches');
    Schema::dropIfExists('warehouses');
    Schema::dropIfExists('products');
});

it('blocks outbound from locked or scrap warehouse types', function () {
    DB::table('warehouses')->insert([
        'id' => 10,
        'company_id' => 1,
        'name' => 'Locked WH',
        'warehouse_type' => 'locked',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('warehouse_product_batches')->insert([
        'company_id' => 1,
        'warehouse_id' => 10,
        'product_id' => 100,
        'batch_number' => 'B-1',
        'quantity' => 5,
        'reserved_quantity' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = app(StockMovementService::class);

    expect(fn() => $service->recordOutbound([
        'company_id' => 1,
        'warehouse_id' => 10,
        'product_id' => 100,
        'quantity' => 1,
        'reference_type' => SalesDo::class,
        'reference_id' => 501,
    ]))->toThrow(WarehouseBusinessException::class);
});

it('prevents oversell when 2 orders reserve nearly at the same time', function () {
    DB::table('warehouses')->insert([
        'id' => 11,
        'company_id' => 1,
        'name' => 'Sellable WH',
        'warehouse_type' => 'normal',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('warehouse_product_batches')->insert([
        'company_id' => 1,
        'warehouse_id' => 11,
        'product_id' => 100,
        'batch_number' => null,
        'quantity' => 10,
        'reserved_quantity' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $shipmentId1 = DB::table('sales_dos')->insertGetId([
        'company_id' => 1,
        'warehouse_id' => 11,
        'status' => 'draft',
        'do_number' => 'DO-001',
        'do_date' => now()->toDateString(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $shipmentId2 = DB::table('sales_dos')->insertGetId([
        'company_id' => 1,
        'warehouse_id' => 11,
        'status' => 'draft',
        'do_number' => 'DO-002',
        'do_date' => now()->toDateString(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('sales_do_items')->insert([
        [
            'sales_do_id' => $shipmentId1,
            'product_id' => 100,
            'quantity_shipped' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'sales_do_id' => $shipmentId2,
            'product_id' => 100,
            'quantity_shipped' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $service = app(SalesDoService::class);
    $shipment1 = SalesDo::with('items')->findOrFail($shipmentId1);
    $shipment2 = SalesDo::with('items')->findOrFail($shipmentId2);

    expect($service->confirm($shipment1))->toBeNull();
    expect(fn() => $service->confirm($shipment2))->toThrow(RuntimeException::class);
});

it('guards double inbound config to avoid duplicate posting', function () {
    Config::set('warehouse.inbound_from_purchase_order_delivered', true);
    Config::set('warehouse.inbound_from_delivery_order_received', true);

    DB::table('warehouses')->insert([
        'id' => 12,
        'company_id' => 1,
        'name' => 'Inbound WH',
        'warehouse_type' => 'normal',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $observer = new PurchaseOrderObserver;
    $po = new PurchaseOrder([
        'id' => 990,
        'company_id' => 1,
        'warehouse_id' => 12,
    ]);
    $po->exists = true;

    $method = (new ReflectionClass(PurchaseOrderObserver::class))->getMethod('recordPurchaseOrderInbound');
    $method->setAccessible(true);

    expect(fn() => $method->invoke($observer, $po, 100, 5.0))
        ->toThrow(WarehouseBusinessException::class);
    expect(DB::table('stock_movements')->count())->toBe(0);
});

it('releases reservations when shipment is cancelled', function () {
    DB::table('warehouses')->insert([
        'id' => 13,
        'company_id' => 1,
        'name' => 'Normal WH',
        'warehouse_type' => 'normal',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('warehouse_product_batches')->insert([
        'company_id' => 1,
        'warehouse_id' => 13,
        'product_id' => 100,
        'batch_number' => null,
        'quantity' => 8,
        'reserved_quantity' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $shipmentId = DB::table('sales_dos')->insertGetId([
        'company_id' => 1,
        'warehouse_id' => 13,
        'status' => 'draft',
        'do_number' => 'DO-013',
        'do_date' => now()->toDateString(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('sales_do_items')->insert([
        'sales_do_id' => $shipmentId,
        'product_id' => 100,
        'quantity_shipped' => 5,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = app(SalesDoService::class);
    $shipment = SalesDo::with('items')->findOrFail($shipmentId);
    $service->confirm($shipment);
    $shipment = SalesDo::with('items')->findOrFail($shipmentId);
    $service->cancel($shipment);

    expect((float) DB::table('warehouse_product_batches')->where('warehouse_id', 13)->value('reserved_quantity'))->toBe(0.0);
    expect(DB::table('stock_reservations')->where('reference_id', $shipmentId)->where('status', 'released')->count())->toBe(1);
});

it('returns correct sellable and available quantities from unified service', function () {
    DB::table('warehouses')->insert([
        [
            'id' => 14,
            'company_id' => 1,
            'name' => 'Sell WH',
            'warehouse_type' => 'normal',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 15,
            'company_id' => 1,
            'name' => 'Locked WH',
            'warehouse_type' => 'locked',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    DB::table('warehouse_product_batches')->insert([
        [
            'company_id' => 1,
            'warehouse_id' => 14,
            'product_id' => 100,
            'batch_number' => 'B14',
            'quantity' => 10,
            'reserved_quantity' => 4,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'company_id' => 1,
            'warehouse_id' => 15,
            'product_id' => 100,
            'batch_number' => 'B15',
            'quantity' => 5,
            'reserved_quantity' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $result = app(WarehouseAvailabilityService::class)->availabilityByProduct(1, 100);

    expect($result['total_available'])->toBe(11.0);
    expect($result['total_sellable'])->toBe(6.0);
    expect($result['sellable_yes_no'])->toBe('YES');
    expect(collect($result['warehouses'])->firstWhere('warehouse_id', 15)['sellable'])->toBe(0.0);
});

it('validates AI webhook order lines against sellable stock', function () {
    DB::table('warehouses')->insert([
        'id' => 20,
        'company_id' => 1,
        'name' => 'WH-Webhook',
        'code' => 'WH20',
        'warehouse_type' => 'normal',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('warehouse_product_batches')->insert([
        'company_id' => 1,
        'warehouse_id' => 20,
        'product_id' => 100,
        'batch_number' => 'B20',
        'quantity' => 10,
        'reserved_quantity' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = app(WarehouseAvailabilityService::class);
    $service->validateAiOrderWebhookItems(1, [
        ['product_id' => 100, 'quantity' => 9],
    ], []);

    expect(fn() => $service->validateAiOrderWebhookItems(1, [
        ['product_id' => 100, 'quantity' => 11],
    ], []))->toThrow(WarehouseBusinessException::class);
});

it('guards invalid outbound mode to prevent ambiguous double deduction flows', function () {
    Config::set('warehouse.sales_outbound_mode', 'both');

    expect(fn() => app(InvoiceWarehouseStockService::class)->shouldPostOutboundFromInvoice())
        ->toThrow(WarehouseBusinessException::class);
});

it('QA smoke: ship posts outbound stock_movement referencing SalesDo; shipment mode skips invoice outbound', function () {
    DB::table('warehouses')->insert([
        'id' => 16,
        'company_id' => 1,
        'name' => 'Ship WH',
        'warehouse_type' => 'normal',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('warehouse_product_batches')->insert([
        'company_id' => 1,
        'warehouse_id' => 16,
        'product_id' => 100,
        'batch_number' => null,
        'quantity' => 20,
        'reserved_quantity' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $shipmentId = DB::table('sales_dos')->insertGetId([
        'company_id' => 1,
        'warehouse_id' => 16,
        'status' => 'draft',
        'do_number' => 'DO-QA-16',
        'do_date' => now()->toDateString(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('sales_do_items')->insert([
        'sales_do_id' => $shipmentId,
        'product_id' => 100,
        'quantity_shipped' => 3,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Config::set('warehouse.sales_outbound_mode', 'shipment');

    $salesDoService = app(SalesDoService::class);
    $shipment = SalesDo::with('items')->findOrFail($shipmentId);
    expect($salesDoService->confirm($shipment))->toBeNull();
    $shipment = SalesDo::with('items')->findOrFail($shipmentId);
    expect($salesDoService->ship($shipment))->toBeNull();

    expect(app(InvoiceWarehouseStockService::class)->shouldPostOutboundFromInvoice())->toBeFalse();

    $movement = DB::table('stock_movements')
        ->where('movement_type', 'outbound')
        ->where('reference_id', $shipmentId)
        ->where('reference_type', SalesDo::class)
        ->first();

    expect($movement)->not->toBeNull();
    expect((float) $movement->quantity)->toBe(3.0);
});
