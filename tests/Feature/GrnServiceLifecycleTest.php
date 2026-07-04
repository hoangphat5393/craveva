<?php

use App\Models\DeliveryOrder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Services\GrnService;

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::create('purchase_orders', function ($table) {
        $table->id();
        $table->unsignedBigInteger('warehouse_id')->nullable();
        $table->string('delivery_status')->default('pending');
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
        $table->string('erp_shipment_reference')->nullable();
        $table->string('wms_shipment_reference')->nullable();
        $table->decimal('delivery_fee', 20, 4)->nullable();
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
        $table->string('batch_number')->nullable();
        $table->date('expiry_date')->nullable();
        $table->string('picking_rule_applied')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('delivery_order_items');
    Schema::dropIfExists('delivery_orders');
    Schema::dropIfExists('purchase_orders');
});

it('accepts valid grn statuses in lifecycle', function () {
    $service = app(GrnService::class);
    DB::table('purchase_orders')->insert([
        'id' => 7001,
        'warehouse_id' => 5,
        'delivery_status' => 'pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $delivery = DeliveryOrder::create([
        'company_id' => 10,
        'purchase_order_id' => 7001,
        'type' => 'inbound',
        'delivery_number' => 'GRN-1001',
        'delivery_date' => now()->toDateString(),
        'status' => 'draft',
    ]);

    expect($service->changeStatus($delivery, 'inbound'))->toBeNull();
    expect($delivery->fresh()->status)->toBe('inbound');

    expect($service->changeStatus($delivery->fresh(), 'received'))->toBeNull();
    expect($delivery->fresh()->status)->toBe('received');
});

it('rejects invalid grn status change', function () {
    $service = app(GrnService::class);
    DB::table('purchase_orders')->insert([
        'id' => 7002,
        'warehouse_id' => 5,
        'delivery_status' => 'pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $delivery = DeliveryOrder::create([
        'company_id' => 10,
        'purchase_order_id' => 7002,
        'type' => 'inbound',
        'delivery_number' => 'GRN-1002',
        'delivery_date' => now()->toDateString(),
        'status' => 'draft',
    ]);

    $result = $service->changeStatus($delivery, 'cancelled');
    expect($result)->toBe('messages.invalidRequest');
    expect($delivery->fresh()->status)->toBe('draft');
});

it('does not move a received grn back to an earlier status', function () {
    $service = app(GrnService::class);
    DB::table('purchase_orders')->insert([
        'id' => 7003,
        'warehouse_id' => 5,
        'delivery_status' => 'pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $delivery = DeliveryOrder::create([
        'company_id' => 10,
        'purchase_order_id' => 7003,
        'type' => 'inbound',
        'delivery_number' => 'GRN-1003',
        'delivery_date' => now()->toDateString(),
        'status' => 'received',
    ]);

    expect($service->changeStatus($delivery, 'draft'))->toBe('purchase::app.grnReceivedImmutable')
        ->and($delivery->fresh()->status)->toBe('received');
});
