<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Entities\SalesDoItem;
use Modules\Warehouse\Entities\WarehouseProductBatch;

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::create('warehouse_product_batches', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('warehouse_id')->nullable();
        $table->unsignedBigInteger('product_id')->nullable();
        $table->string('batch_number')->nullable();
        $table->date('expiration_date')->nullable();
        $table->decimal('quantity', 20, 4)->default(0);
        $table->decimal('reserved_quantity', 20, 4)->default(0);
        $table->timestamps();
    });

    Schema::create('sales_do_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('sales_do_id')->nullable();
        $table->unsignedBigInteger('order_item_id')->nullable();
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
    Schema::dropIfExists('warehouse_product_batches');
});

it('returns explicit batch number when sales do item has one', function () {
    $item = SalesDoItem::create([
        'sales_do_id' => 1,
        'order_item_id' => 1,
        'batch_number' => 'BN-LOCAL-001',
    ]);

    expect($item->batch_display)->toBe('BN-LOCAL-001');
});

it('falls back to warehouse batch number when local batch number is empty', function () {
    $batch = WarehouseProductBatch::query()->create([
        'company_id' => 1,
        'warehouse_id' => 1,
        'product_id' => 1,
        'batch_number' => 'BN-WH-009',
    ]);

    $item = SalesDoItem::create([
        'sales_do_id' => 1,
        'order_item_id' => 2,
        'warehouse_batch_id' => $batch->id,
        'batch_number' => null,
    ]);

    expect($item->fresh()->batch_display)->toBe('BN-WH-009');
});

it('falls back to batch id label when no batch number exists', function () {
    $batch = WarehouseProductBatch::query()->create([
        'company_id' => 1,
        'warehouse_id' => 1,
        'product_id' => 1,
        'batch_number' => null,
    ]);

    $item = SalesDoItem::create([
        'sales_do_id' => 1,
        'order_item_id' => 3,
        'warehouse_batch_id' => $batch->id,
        'batch_number' => null,
    ]);

    expect($item->fresh()->batch_display)->toBe('Batch#'.$batch->id);
});
