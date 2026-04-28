<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Entities\SalesShipment;
use Modules\Purchase\Entities\SalesShipmentItem;
use Modules\Purchase\Services\SalesDoService;
use Modules\Warehouse\Services\SalesShipmentStockService;

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    session(['user' => (object) ['id' => 999]]);

    Schema::create('orders', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->timestamps();
    });

    DB::table('orders')->insert([
        ['id' => 1, 'company_id' => 10, 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'company_id' => 10, 'created_at' => now(), 'updated_at' => now()],
        ['id' => 3, 'company_id' => 10, 'created_at' => now(), 'updated_at' => now()],
    ]);

    Schema::create('warehouses', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->string('name')->nullable();
        $table->timestamps();
    });

    DB::table('warehouses')->insert([
        ['id' => 1, 'company_id' => 10, 'name' => 'WH-1', 'created_at' => now(), 'updated_at' => now()],
    ]);

    Schema::create('sales_shipments', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('order_id');
        $table->unsignedBigInteger('warehouse_id');
        $table->string('shipment_number');
        $table->date('shipment_date');
        $table->string('status')->default('draft');
        $table->boolean('outbound_stock_applied')->default(false);
        $table->text('notes')->nullable();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->timestamps();
    });

    Schema::create('sales_shipment_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('sales_shipment_id');
        $table->unsignedBigInteger('order_item_id');
        $table->unsignedBigInteger('product_id')->nullable();
        $table->decimal('quantity_ordered', 20, 4)->default(0);
        $table->decimal('quantity_shipped', 20, 4)->default(0);
        $table->unsignedBigInteger('unit_id')->nullable();
        $table->string('batch_number')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Mockery::close();
    Schema::dropIfExists('sales_shipment_items');
    Schema::dropIfExists('sales_shipments');
    Schema::dropIfExists('warehouses');
    Schema::dropIfExists('orders');
});

it('confirms shipment only when draft with items', function () {
    $mockStock = Mockery::mock(SalesShipmentStockService::class);
    $service = new SalesDoService($mockStock);

    $shipment = SalesShipment::create([
        'company_id' => 10,
        'order_id' => 1,
        'warehouse_id' => 1,
        'shipment_number' => 'SS-1',
        'shipment_date' => now()->toDateString(),
        'status' => 'draft',
    ]);

    expect($service->confirm($shipment->fresh('items')))->toBe('messages.addItem');

    SalesShipmentItem::create([
        'sales_shipment_id' => $shipment->id,
        'order_item_id' => 100,
        'product_id' => 200,
        'quantity_ordered' => 10,
        'quantity_shipped' => 2,
    ]);

    $mockStock->shouldReceive('ensureReservationsForShipment')->once();

    expect($service->confirm($shipment->fresh('items')))->toBeNull();
    expect($shipment->fresh()->status)->toBe('confirmed');
    expect((int) $shipment->fresh()->updated_by)->toBe(999);
});

it('ships shipment and triggers outbound once', function () {
    $mockStock = Mockery::mock(SalesShipmentStockService::class);
    $service = new SalesDoService($mockStock);

    $shipment = SalesShipment::create([
        'company_id' => 10,
        'order_id' => 2,
        'warehouse_id' => 1,
        'shipment_number' => 'SS-2',
        'shipment_date' => now()->toDateString(),
        'status' => 'confirmed',
    ]);

    SalesShipmentItem::create([
        'sales_shipment_id' => $shipment->id,
        'order_item_id' => 101,
        'product_id' => 201,
        'quantity_ordered' => 10,
        'quantity_shipped' => 3,
    ]);

    $mockStock->shouldReceive('ensureReservationsForShipment')->once();
    $mockStock->shouldReceive('applyOutboundForShipment')->once()->withArgs(function ($arg) use ($shipment) {
        return (int) $arg->id === (int) $shipment->id;
    });

    expect($service->ship($shipment->fresh('items')))->toBeNull();
    expect($shipment->fresh()->status)->toBe('shipped');
});

it('ship returns a dedicated translation key when total shipped quantity is zero', function () {
    $mockStock = Mockery::mock(SalesShipmentStockService::class);
    $service = new SalesDoService($mockStock);

    $shipment = SalesShipment::create([
        'company_id' => 10,
        'order_id' => 2,
        'warehouse_id' => 1,
        'shipment_number' => 'SS-Z',
        'shipment_date' => now()->toDateString(),
        'status' => 'confirmed',
    ]);

    SalesShipmentItem::create([
        'sales_shipment_id' => $shipment->id,
        'order_item_id' => 101,
        'product_id' => 201,
        'quantity_ordered' => 10,
        'quantity_shipped' => 0,
    ]);

    $mockStock->shouldNotReceive('ensureReservationsForShipment');
    $mockStock->shouldNotReceive('applyOutboundForShipment');

    expect($service->ship($shipment->fresh('items')))->toBe('messages.salesDoShipQuantityRequired');
});

it('delivers, reverses and cancels shipment with expected guards', function () {
    $mockStock = Mockery::mock(SalesShipmentStockService::class);
    $service = new SalesDoService($mockStock);

    $shipment = SalesShipment::create([
        'company_id' => 10,
        'order_id' => 3,
        'warehouse_id' => 1,
        'shipment_number' => 'SS-3',
        'shipment_date' => now()->toDateString(),
        'status' => 'draft',
        'outbound_stock_applied' => true,
    ]);

    expect($service->deliver($shipment->fresh()))->toBe('messages.invalidRequest');
    expect($service->reverse($shipment->fresh()))->toBe('messages.invalidRequest');

    $shipment->update(['status' => 'shipped']);
    expect($service->deliver($shipment->fresh()))->toBeNull();
    expect($shipment->fresh()->status)->toBe('delivered');

    $mockStock->shouldReceive('reverseOutboundForShipment')->once();
    $mockStock->shouldReceive('ensureReservationsForShipment')->once();
    expect($service->reverse($shipment->fresh()))->toBeNull();
    expect($shipment->fresh()->status)->toBe('confirmed');

    $shipment->update(['status' => 'cancelled', 'outbound_stock_applied' => true]);
    expect($service->cancel($shipment->fresh()))->toBeNull();
    // already cancelled path should not trigger reverse again
});

it('blocks confirm when sales order or warehouse link is missing on the header', function () {
    $mockStock = Mockery::mock(SalesShipmentStockService::class);
    $service = new SalesDoService($mockStock);

    $shipment = SalesShipment::create([
        'company_id' => 10,
        'order_id' => 1,
        'warehouse_id' => 1,
        'shipment_number' => 'SS-BAD-ORDER',
        'shipment_date' => now()->toDateString(),
        'status' => 'draft',
    ]);

    SalesShipmentItem::create([
        'sales_shipment_id' => $shipment->id,
        'order_item_id' => 100,
        'product_id' => 200,
        'quantity_ordered' => 10,
        'quantity_shipped' => 2,
    ]);

    $shipment->forceFill(['order_id' => 0])->saveQuietly();

    $mockStock->shouldNotReceive('ensureReservationsForShipment');

    expect($service->confirm($shipment->fresh('items')))->toBe('messages.salesDoHeaderRequiresOrderAndWarehouse');
});

it('blocks ship when warehouse id is missing on the header', function () {
    $mockStock = Mockery::mock(SalesShipmentStockService::class);
    $service = new SalesDoService($mockStock);

    $shipment = SalesShipment::create([
        'company_id' => 10,
        'order_id' => 1,
        'warehouse_id' => 1,
        'shipment_number' => 'SS-BAD-WH',
        'shipment_date' => now()->toDateString(),
        'status' => 'confirmed',
    ]);

    SalesShipmentItem::create([
        'sales_shipment_id' => $shipment->id,
        'order_item_id' => 100,
        'product_id' => 200,
        'quantity_ordered' => 10,
        'quantity_shipped' => 3,
    ]);

    $shipment->forceFill(['warehouse_id' => 0])->saveQuietly();

    $mockStock->shouldNotReceive('ensureReservationsForShipment');
    $mockStock->shouldNotReceive('applyOutboundForShipment');

    expect($service->ship($shipment->fresh('items')))->toBe('messages.salesDoHeaderRequiresOrderAndWarehouse');
});

it('blocks confirm when linked order does not exist for the shipment company', function () {
    $mockStock = Mockery::mock(SalesShipmentStockService::class);
    $service = new SalesDoService($mockStock);

    $shipment = SalesShipment::create([
        'company_id' => 10,
        'order_id' => 1,
        'warehouse_id' => 1,
        'shipment_number' => 'SS-ORPHAN-ORDER',
        'shipment_date' => now()->toDateString(),
        'status' => 'draft',
    ]);

    SalesShipmentItem::create([
        'sales_shipment_id' => $shipment->id,
        'order_item_id' => 100,
        'product_id' => 200,
        'quantity_ordered' => 10,
        'quantity_shipped' => 1,
    ]);

    $shipment->forceFill(['order_id' => 99999])->saveQuietly();

    $mockStock->shouldNotReceive('ensureReservationsForShipment');

    expect($service->confirm($shipment->fresh('items')))->toBe('messages.salesDoHeaderOrderNotFoundForCompany');
});
