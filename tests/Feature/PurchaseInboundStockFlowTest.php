<?php

use App\Models\DeliveryOrder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Entities\PurchaseOrder;
use Modules\Purchase\Observers\DeliveryOrderObserver;
use Modules\Purchase\Observers\PurchaseOrderObserver;

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::create('warehouses', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('name')->nullable();
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

    Schema::create('delivery_orders', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('purchase_order_id')->nullable();
        $table->string('type')->nullable();
        $table->string('delivery_number')->nullable();
        $table->date('delivery_date')->nullable();
        $table->unsignedBigInteger('warehouse_id')->nullable();
        $table->string('status')->default('draft');
        $table->boolean('inbound_stock_applied')->default(false);
        $table->timestamps();
    });

    Schema::create('delivery_order_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('delivery_order_id');
        $table->unsignedBigInteger('purchase_item_id')->nullable();
        $table->unsignedBigInteger('product_id')->nullable();
        $table->double('quantity_ordered')->default(0);
        $table->double('quantity_received')->default(0);
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
});

afterEach(function () {
    Schema::dropIfExists('delivery_order_items');
    Schema::dropIfExists('delivery_orders');
    Schema::dropIfExists('stock_movements');
    Schema::dropIfExists('warehouse_product_stock');
    Schema::dropIfExists('warehouse_product_batches');
    Schema::dropIfExists('products');
    Schema::dropIfExists('warehouses');
});

it('posts inbound stock when inbound DO is received and WAREHOUSE_INBOUND_FROM_DO_RECEIVED is true', function () {
    Config::set('warehouse.inbound_from_delivery_order_received', true);
    Config::set('warehouse.inbound_from_purchase_order_delivered', false);

    $doId = DB::table('delivery_orders')->insertGetId([
        'company_id' => 10,
        'purchase_order_id' => null,
        'type' => 'inbound',
        'status' => 'received',
        'warehouse_id' => 1,
        'inbound_stock_applied' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $itemId = DB::table('delivery_order_items')->insertGetId([
        'delivery_order_id' => $doId,
        'purchase_item_id' => null,
        'product_id' => 99,
        'quantity_ordered' => 5,
        'quantity_received' => 5,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $deliveryOrder = DeliveryOrder::with('items')->findOrFail($doId);
    app(DeliveryOrderObserver::class)->saved($deliveryOrder);

    $qty = (float) DB::table('warehouse_product_stock')
        ->where('warehouse_id', 1)
        ->where('product_id', 99)
        ->value('quantity');
    expect($qty)->toBe(5.0);

    $movement = DB::table('stock_movements')
        ->where('movement_type', 'inbound')
        ->where('reference_type', DeliveryOrder::class)
        ->where('reference_id', $doId)
        ->where('delivery_order_item_id', $itemId)
        ->first();
    expect($movement)->not->toBeNull();
    expect((float) $movement->quantity)->toBe(5.0);

    expect((bool) DB::table('delivery_orders')->where('id', $doId)->value('inbound_stock_applied'))->toBeTrue();
});

it('posts inbound stock from purchase order path when WAREHOUSE_INBOUND_FROM_PO_DELIVERED is true', function () {
    Config::set('warehouse.inbound_from_purchase_order_delivered', true);

    $observer = new PurchaseOrderObserver;
    $po = new PurchaseOrder([
        'id' => 700,
        'company_id' => 10,
        'warehouse_id' => 1,
    ]);
    $po->exists = true;

    $method = (new \ReflectionClass(PurchaseOrderObserver::class))->getMethod('recordPurchaseOrderInbound');
    $method->setAccessible(true);
    $method->invoke($observer, $po, 99, 6.0);

    $qty = (float) DB::table('warehouse_product_stock')
        ->where('warehouse_id', 1)
        ->where('product_id', 99)
        ->value('quantity');
    expect($qty)->toBe(6.0);

    expect(DB::table('stock_movements')
        ->where('movement_type', 'inbound')
        ->where('reference_type', PurchaseOrder::class)
        ->where('reference_id', 700)
        ->count())->toBe(1);
});

it('skips purchase order inbound when WAREHOUSE_INBOUND_FROM_PO_DELIVERED is false', function () {
    Config::set('warehouse.inbound_from_purchase_order_delivered', false);

    $observer = new PurchaseOrderObserver;
    $po = new PurchaseOrder([
        'id' => 701,
        'company_id' => 10,
        'warehouse_id' => 1,
    ]);
    $po->exists = true;

    $method = (new \ReflectionClass(PurchaseOrderObserver::class))->getMethod('recordPurchaseOrderInbound');
    $method->setAccessible(true);
    $method->invoke($observer, $po, 99, 2.0);

    expect(DB::table('stock_movements')->count())->toBe(0);
    expect((float) DB::table('warehouse_product_stock')
        ->where('warehouse_id', 1)
        ->where('product_id', 99)
        ->value('quantity'))->toBe(0.0);
});
