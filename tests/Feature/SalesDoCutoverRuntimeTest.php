<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Entities\SalesDo;
use Modules\Purchase\Services\SalesDoService;

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    Config::set('purchase.do_grn_cutover_enabled', true);
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::create('sales_dos', function ($table) {
        $table->id();
        $table->unsignedBigInteger('legacy_sales_shipment_id')->nullable();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('order_id');
        $table->unsignedBigInteger('warehouse_id');
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
        $table->unsignedBigInteger('legacy_sales_shipment_item_id')->nullable();
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

it('persists sales do data in new tables when cutover is enabled', function () {
    $service = app(SalesDoService::class);
    $shipment = $service->create([
        'order_id' => 2001,
        'warehouse_id' => 5,
        'shipment_number' => 'DO-2001',
        'shipment_date' => now()->toDateString(),
        'status' => 'draft',
        'notes' => 'cutover runtime',
        'order_item_id' => [9001],
        'product_id' => [701],
        'quantity_ordered' => [10],
        'quantity_shipped' => [3],
        'unit_id' => [1],
        'batch_number' => ['CUTOVER-B1'],
    ], 10, 77);

    expect($shipment)->toBeInstanceOf(SalesDo::class);
    expect(DB::table('sales_dos')->count())->toBe(1);
    expect(DB::table('sales_do_items')->where('sales_do_id', $shipment->id)->count())->toBe(1);
    expect(DB::table('sales_dos')->where('id', $shipment->id)->value('do_number'))->toBe('DO-2001');
});
