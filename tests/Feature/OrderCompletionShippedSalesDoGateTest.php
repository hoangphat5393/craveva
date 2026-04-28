<?php

use App\Models\Order;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Warehouse\Services\OrderCompletionShippedSalesDoGate;

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Config::set('warehouse.sales_outbound_enabled', true);
    Config::set('warehouse.sales_outbound_mode', 'shipment');

    Schema::create('orders', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->string('status')->default('pending');
        $table->timestamps();
    });

    Schema::create('order_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('order_id');
        $table->unsignedBigInteger('product_id')->nullable();
        $table->string('item_name')->default('x');
        $table->string('type')->nullable();
        $table->decimal('quantity', 12, 4)->default(1);
        $table->decimal('unit_price', 12, 2)->default(0);
        $table->decimal('amount', 12, 2)->default(0);
        $table->timestamps();
    });

    Schema::create('order_item_images', function ($table) {
        $table->id();
        $table->unsignedBigInteger('order_item_id');
        $table->timestamps();
    });

    Schema::create('products', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->string('name')->nullable();
        $table->string('type')->default('goods');
        $table->boolean('track_inventory')->default(true);
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
        $table->timestamps();
    });

    Schema::create('sales_do_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('sales_do_id');
        $table->unsignedBigInteger('order_item_id')->nullable();
        $table->unsignedBigInteger('product_id')->nullable();
        $table->decimal('quantity_ordered', 20, 4)->default(0);
        $table->decimal('quantity_shipped', 20, 4)->default(0);
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('sales_do_items');
    Schema::dropIfExists('sales_dos');
    Schema::dropIfExists('order_item_images');
    Schema::dropIfExists('order_items');
    Schema::dropIfExists('products');
    Schema::dropIfExists('orders');
});

it('blocks completion when a tracked goods line is not fully shipped on a DO', function () {
    $productId = DB::table('products')->insertGetId([
        'company_id' => 1,
        'name' => 'Widget',
        'type' => 'goods',
        'track_inventory' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $orderId = DB::table('orders')->insertGetId([
        'company_id' => 1,
        'status' => 'processing',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $orderItemId = DB::table('order_items')->insertGetId([
        'order_id' => $orderId,
        'product_id' => $productId,
        'item_name' => 'Widget',
        'type' => 'item',
        'quantity' => 5,
        'unit_price' => 10,
        'amount' => 50,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $order = Order::with('items.product')->findOrFail($orderId);
    $gate = app(OrderCompletionShippedSalesDoGate::class);

    expect($gate->blockingMessage($order))->not->toBeNull();

    $doId = DB::table('sales_dos')->insertGetId([
        'company_id' => 1,
        'order_id' => $orderId,
        'warehouse_id' => 1,
        'do_number' => 'DO-1',
        'do_date' => now()->toDateString(),
        'status' => 'draft',
        'outbound_stock_applied' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('sales_do_items')->insert([
        'sales_do_id' => $doId,
        'order_item_id' => $orderItemId,
        'product_id' => $productId,
        'quantity_ordered' => 5,
        'quantity_shipped' => 5,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $order->refresh()->load('items.product');
    expect($gate->blockingMessage($order))->not->toBeNull();

    DB::table('sales_dos')->where('id', $doId)->update(['status' => 'shipped']);

    $order->refresh()->load('items.product');
    expect($gate->blockingMessage($order))->toBeNull();
});

it('ignores the gate when outbound mode is invoice', function () {
    Config::set('warehouse.sales_outbound_mode', 'invoice');

    $productId = DB::table('products')->insertGetId([
        'company_id' => 1,
        'name' => 'Widget',
        'type' => 'goods',
        'track_inventory' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $orderId = DB::table('orders')->insertGetId([
        'company_id' => 1,
        'status' => 'processing',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('order_items')->insert([
        'order_id' => $orderId,
        'product_id' => $productId,
        'item_name' => 'Widget',
        'type' => 'item',
        'quantity' => 3,
        'unit_price' => 1,
        'amount' => 3,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $order = Order::with('items.product')->findOrFail($orderId);
    $gate = app(OrderCompletionShippedSalesDoGate::class);

    expect($gate->blockingMessage($order))->toBeNull();
});

it('skips lines for service products', function () {
    $productId = DB::table('products')->insertGetId([
        'company_id' => 1,
        'name' => 'Consulting',
        'type' => 'service',
        'track_inventory' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $orderId = DB::table('orders')->insertGetId([
        'company_id' => 1,
        'status' => 'processing',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('order_items')->insert([
        'order_id' => $orderId,
        'product_id' => $productId,
        'item_name' => 'Consulting',
        'type' => 'item',
        'quantity' => 1,
        'unit_price' => 100,
        'amount' => 100,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $order = Order::with('items.product')->findOrFail($orderId);
    $gate = app(OrderCompletionShippedSalesDoGate::class);

    expect($gate->blockingMessage($order))->toBeNull();
});
