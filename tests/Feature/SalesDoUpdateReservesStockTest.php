<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Entities\SalesDo;
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
        'id' => 1,
        'company_id' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Schema::create('sales_dos', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->unsignedBigInteger('order_id');
        $table->unsignedBigInteger('warehouse_id');
        $table->string('do_number', 64);
        $table->date('do_date');
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
        $table->unsignedBigInteger('order_item_id');
        $table->unsignedInteger('product_id')->nullable();
        $table->decimal('quantity_ordered', 20, 4)->default(0);
        $table->decimal('quantity_shipped', 20, 4)->default(0);
        $table->unsignedBigInteger('unit_id')->nullable();
        $table->unsignedBigInteger('warehouse_batch_id')->nullable();
        $table->string('batch_number', 191)->nullable();
        $table->date('expiration_date')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Mockery::close();
    Schema::dropIfExists('sales_do_items');
    Schema::dropIfExists('sales_dos');
    Schema::dropIfExists('orders');
});

it('calls ensure reservations when update moves sales do from draft to confirmed', function () {
    $mockStock = Mockery::mock(SalesShipmentStockService::class);
    $service = new SalesDoService($mockStock);

    $do = SalesDo::create([
        'company_id' => 10,
        'order_id' => 1,
        'warehouse_id' => 1,
        'do_number' => 'SS-UPD-RES',
        'do_date' => now()->toDateString(),
        'status' => 'draft',
        'created_by' => 1,
        'updated_by' => 1,
    ]);

    DB::table('sales_do_items')->insert([
        'sales_do_id' => $do->id,
        'order_item_id' => 100,
        'product_id' => 200,
        'quantity_ordered' => 10,
        'quantity_shipped' => 3,
        'unit_id' => null,
        'warehouse_batch_id' => null,
        'batch_number' => null,
        'expiration_date' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $payload = [
        'order_id' => 1,
        'warehouse_id' => 1,
        'shipment_number' => 'SS-UPD-RES',
        'shipment_date' => now()->toDateString(),
        'status' => 'confirmed',
        'notes' => null,
        'order_item_id' => [100],
        'product_id' => [200],
        'unit_id' => [null],
        'quantity_ordered' => [10],
        'quantity_shipped' => [3],
        'batch_number' => [null],
        'warehouse_batch_id' => [null],
        'expiration_date' => [null],
    ];

    $mockStock->shouldReceive('ensureReservationsForShipment')->once();

    $service->update($do->fresh(['items']), $payload, 999);

    expect($do->fresh()->status)->toBe('confirmed');
});

it('calls ensure reservations when create saves a new sales do as confirmed', function () {
    $mockStock = Mockery::mock(SalesShipmentStockService::class);
    $service = new SalesDoService($mockStock);

    $payload = [
        'order_id' => 1,
        'warehouse_id' => 1,
        'shipment_number' => 'SS-NEW-RES',
        'shipment_date' => now()->toDateString(),
        'status' => 'confirmed',
        'notes' => null,
        'order_item_id' => [100],
        'product_id' => [200],
        'unit_id' => [null],
        'quantity_ordered' => [10],
        'quantity_shipped' => [2],
        'batch_number' => [null],
        'warehouse_batch_id' => [null],
        'expiration_date' => [null],
    ];

    $mockStock->shouldReceive('ensureReservationsForShipment')->once();

    $created = $service->create($payload, 10, 999);

    expect($created->status)->toBe('confirmed');
});
