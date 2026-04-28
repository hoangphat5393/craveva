<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Entities\SalesDo;
use Modules\Purchase\Services\SalesDoService;

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::create('sales_dos', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('order_id');
        $table->unsignedBigInteger('warehouse_id');
        $table->string('do_number');
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
        $table->unsignedBigInteger('product_id')->nullable();
        $table->decimal('quantity_ordered', 20, 4)->default(0);
        $table->decimal('quantity_shipped', 20, 4)->default(0);
        $table->unsignedBigInteger('unit_id')->nullable();
        $table->unsignedBigInteger('warehouse_batch_id')->nullable();
        $table->string('batch_number')->nullable();
        $table->date('expiration_date')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('sales_do_items');
    Schema::dropIfExists('sales_dos');
});

it('creates sales do header and items via service', function () {
    $service = app(SalesDoService::class);
    $shipment = $service->create([
        'order_id' => 1001,
        'warehouse_id' => 7,
        'shipment_number' => 'SS-000777',
        'shipment_date' => now()->toDateString(),
        'status' => 'draft',
        'notes' => 'phase2 create',
        'order_item_id' => [501, 502],
        'product_id' => [99, 100],
        'quantity_ordered' => [10, 20],
        'quantity_shipped' => [2, 5],
        'unit_id' => [1, 1],
        'batch_number' => ['B-1', 'B-2'],
    ], 10, 99);

    expect($shipment)->toBeInstanceOf(SalesDo::class);
    expect((int) $shipment->company_id)->toBe(10);
    expect($shipment->do_number)->toBe('SS-000777');
    expect((int) $shipment->created_by)->toBe(99);
    expect(DB::table('sales_do_items')->where('sales_do_id', $shipment->id)->count())->toBe(2);
});

it('updates sales do and replaces items via service', function () {
    $service = app(SalesDoService::class);
    $shipment = $service->create([
        'order_id' => 1002,
        'warehouse_id' => 1,
        'shipment_number' => 'SS-000888',
        'shipment_date' => now()->toDateString(),
        'status' => 'draft',
        'notes' => null,
        'order_item_id' => [601],
        'product_id' => [201],
        'quantity_ordered' => [8],
        'quantity_shipped' => [3],
        'unit_id' => [1],
        'batch_number' => ['OLD-BATCH'],
    ], 10, 99);

    $updated = $service->update($shipment->fresh(), [
        'order_id' => 1002,
        'warehouse_id' => 2,
        'shipment_number' => 'SS-000888',
        'shipment_date' => now()->toDateString(),
        'status' => 'confirmed',
        'notes' => 'updated',
        'order_item_id' => [602, 603],
        'product_id' => [202, 203],
        'quantity_ordered' => [12, 14],
        'quantity_shipped' => [6, 4],
        'unit_id' => [1, 1],
        'batch_number' => ['NEW-B1', 'NEW-B2'],
    ], 100);

    expect((int) $updated->warehouse_id)->toBe(2);
    expect($updated->status)->toBe('confirmed');
    expect((int) $updated->updated_by)->toBe(100);
    expect(DB::table('sales_do_items')->where('sales_do_id', $updated->id)->count())->toBe(2);
    expect(DB::table('sales_do_items')->where('sales_do_id', $updated->id)->where('batch_number', 'OLD-BATCH')->count())->toBe(0);
});
