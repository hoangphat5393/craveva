<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Entities\SalesDo;
use Modules\Purchase\Entities\SalesDoItem;
use Modules\Warehouse\Exceptions\WarehouseBusinessException;
use Modules\Warehouse\Services\InvoiceWarehouseStockService;
use Modules\Warehouse\Services\SalesShipmentStockService;

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    Config::set('purchase.do_grn_cutover_enabled', false);
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::create('warehouses', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('name')->nullable();
        $table->string('warehouse_type')->default('normal');
        $table->string('status')->default('active');
        $table->timestamps();
    });

    Schema::create('products', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('name')->nullable();
        $table->string('type')->default('goods');
        $table->timestamps();
    });

    Schema::create('warehouse_product_batches', function ($table) {
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
        $table->unsignedInteger('company_id');
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

    Schema::create('invoice_warehouse_stock_postings', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('invoice_id');
        $table->unsignedBigInteger('invoice_item_id')->nullable();
        $table->unsignedBigInteger('warehouse_id')->nullable();
        $table->unsignedBigInteger('product_id')->nullable();
        $table->decimal('quantity', 20, 4)->default(0);
        $table->timestamps();
    });

    DB::table('warehouses')->insert([
        'id' => 1,
        'company_id' => 10,
        'name' => 'WH-1',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('products')->insert([
        'id' => 99,
        'company_id' => 10,
        'name' => 'SKU-99',
        'type' => 'goods',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('warehouse_product_batches')->insert([
        'company_id' => 10,
        'warehouse_id' => 1,
        'product_id' => 99,
        'batch_number' => 'B-01',
        'quantity' => 10,
        'reserved_quantity' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

afterEach(function () {
    Schema::dropIfExists('invoice_warehouse_stock_postings');
    Schema::dropIfExists('sales_do_items');
    Schema::dropIfExists('sales_dos');
    Schema::dropIfExists('stock_reservations');
    Schema::dropIfExists('stock_movements');
    Schema::dropIfExists('warehouse_product_stock');
    Schema::dropIfExists('warehouse_product_batches');
    Schema::dropIfExists('products');
    Schema::dropIfExists('warehouses');
});

it('posts outbound by shipment, prevents idempotent double post, and blocks over shipment', function () {
    Config::set('warehouse.sales_outbound_mode', 'shipment');
    Config::set('warehouse.allow_negative_stock', false);

    $shipment1 = SalesDo::create([
        'company_id' => 10,
        'order_id' => 1001,
        'warehouse_id' => 1,
        'do_number' => 'DO-000001',
        'do_date' => now()->toDateString(),
        'status' => 'confirmed',
    ]);

    SalesDoItem::create([
        'sales_do_id' => $shipment1->id,
        'order_item_id' => 501,
        'product_id' => 99,
        'quantity_ordered' => 10,
        'quantity_shipped' => 4,
    ]);

    $shipment2 = SalesDo::create([
        'company_id' => 10,
        'order_id' => 1001,
        'warehouse_id' => 1,
        'do_number' => 'DO-000002',
        'do_date' => now()->toDateString(),
        'status' => 'confirmed',
    ]);

    SalesDoItem::create([
        'sales_do_id' => $shipment2->id,
        'order_item_id' => 501,
        'product_id' => 99,
        'quantity_ordered' => 10,
        'quantity_shipped' => 6,
    ]);

    $service = app(SalesShipmentStockService::class);
    $service->applyOutboundForShipment($shipment1);
    $service->applyOutboundForShipment($shipment1->fresh()); // idempotent: should no-op
    $service->applyOutboundForShipment($shipment2);

    $totalOutbound = (float) DB::table('stock_movements')
        ->where('movement_type', 'outbound')
        ->sum('quantity');
    expect($totalOutbound)->toBe(10.0);

    $stock = (float) DB::table('warehouse_product_stock')
        ->where('warehouse_id', 1)
        ->where('product_id', 99)
        ->value('quantity');
    expect($stock)->toBe(0.0);

    $shipment3 = SalesDo::create([
        'company_id' => 10,
        'order_id' => 1001,
        'warehouse_id' => 1,
        'do_number' => 'DO-000003',
        'do_date' => now()->toDateString(),
        'status' => 'confirmed',
    ]);
    SalesDoItem::create([
        'sales_do_id' => $shipment3->id,
        'order_item_id' => 501,
        'product_id' => 99,
        'quantity_ordered' => 10,
        'quantity_shipped' => 1,
    ]);

    $this->expectException(WarehouseBusinessException::class);
    $service->applyOutboundForShipment($shipment3);
});

it('reverses outbound without stock drift', function () {
    Config::set('warehouse.sales_outbound_mode', 'shipment');
    Config::set('warehouse.allow_negative_stock', false);

    $shipment = SalesDo::create([
        'company_id' => 10,
        'order_id' => 1002,
        'warehouse_id' => 1,
        'do_number' => 'DO-000101',
        'do_date' => now()->toDateString(),
        'status' => 'shipped',
    ]);

    SalesDoItem::create([
        'sales_do_id' => $shipment->id,
        'order_item_id' => 601,
        'product_id' => 99,
        'quantity_ordered' => 10,
        'quantity_shipped' => 5,
    ]);

    $service = app(SalesShipmentStockService::class);
    $service->applyOutboundForShipment($shipment);

    $afterShip = (float) DB::table('warehouse_product_stock')
        ->where('warehouse_id', 1)
        ->where('product_id', 99)
        ->value('quantity');
    expect($afterShip)->toBe(5.0);

    $service->reverseOutboundForShipment($shipment->fresh());
    $service->reverseOutboundForShipment($shipment->fresh()); // idempotent no-op

    $afterReverse = (float) DB::table('warehouse_product_stock')
        ->where('warehouse_id', 1)
        ->where('product_id', 99)
        ->value('quantity');
    expect($afterReverse)->toBe(10.0);

    $reversalInbounds = (float) DB::table('stock_movements')
        ->where('movement_type', 'inbound')
        ->where('reference_type', 'sales_shipment_stock_reversal')
        ->where('reference_id', $shipment->id)
        ->sum('quantity');
    expect($reversalInbounds)->toBe(5.0);
});

it('disables invoice outbound posting when shipment mode is active', function () {
    Config::set('warehouse.sales_outbound_mode', 'shipment');
    $svc = app(InvoiceWarehouseStockService::class);
    expect($svc->shouldPostOutboundFromInvoice())->toBeFalse();
});
