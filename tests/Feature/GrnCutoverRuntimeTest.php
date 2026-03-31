<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Services\GrnService;

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    Config::set('purchase.do_grn_cutover_enabled', true);
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::create('grns', function ($table) {
        $table->id();
        $table->unsignedBigInteger('legacy_delivery_order_id')->nullable();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('purchase_order_id')->nullable();
        $table->string('type')->nullable();
        $table->string('grn_number')->nullable();
        $table->date('grn_date')->nullable();
        $table->unsignedBigInteger('warehouse_id')->nullable();
        $table->string('status')->default('draft');
        $table->boolean('inbound_stock_applied')->default(false);
        $table->string('erp_shipment_reference')->nullable();
        $table->string('wms_shipment_reference')->nullable();
        $table->decimal('delivery_fee', 20, 4)->nullable();
        $table->timestamps();
    });

    Schema::create('grn_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('grn_id');
        $table->unsignedBigInteger('legacy_delivery_order_item_id')->nullable();
        $table->unsignedBigInteger('purchase_item_id')->nullable();
        $table->unsignedBigInteger('product_id')->nullable();
        $table->string('batch_number')->nullable();
        $table->date('expiry_date')->nullable();
        $table->string('picking_rule_applied')->nullable();
        $table->decimal('quantity_ordered', 20, 4)->default(0);
        $table->decimal('quantity_received', 20, 4)->default(0);
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('grn_items');
    Schema::dropIfExists('grns');
});

it('persists grn data in new tables when cutover is enabled', function () {
    $service = app(GrnService::class);
    $delivery = $service->create([
        'purchase_order_id' => 7001,
        'warehouse_id' => 5,
        'type' => 'inbound',
        'delivery_number' => 'GRN-2001',
        'delivery_date' => now()->toDateString(),
        'status' => 'draft',
        'item_id' => [301],
        'product_id' => [901],
        'quantity_ordered' => [10],
        'quantity_received' => [4],
    ], 10);

    expect((int) $delivery->company_id)->toBe(10);
    expect($delivery->delivery_number)->toBe('GRN-2001');
    expect(DB::table('grns')->count())->toBe(1);
    expect(DB::table('grn_items')->where('grn_id', $delivery->id)->count())->toBe(1);
});
