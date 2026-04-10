<?php

use App\Models\Grn;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Services\GrnService;

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Config::set('warehouse.inbound_from_delivery_order_received', false);
    Config::set('warehouse.inbound_from_purchase_order_delivered', false);

    Schema::create('grns', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('purchase_order_id')->nullable();
        $table->string('type')->nullable();
        $table->string('grn_number')->nullable();
        $table->date('grn_date')->nullable();
        $table->unsignedBigInteger('warehouse_id')->nullable();
        $table->string('status')->default('draft');
        $table->string('erp_shipment_reference')->nullable();
        $table->string('wms_shipment_reference')->nullable();
        $table->decimal('delivery_fee', 20, 4)->nullable();
        $table->boolean('inbound_stock_applied')->default(false);
        $table->timestamps();
    });

    Schema::create('grn_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('grn_id');
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
    Schema::dropIfExists('grn_items');
    Schema::dropIfExists('grns');
});

it('creates grn header and items via service', function () {
    $service = app(GrnService::class);
    $delivery = $service->create([
        'purchase_order_id' => 7001,
        'warehouse_id' => 5,
        'type' => 'inbound',
        'delivery_number' => 'GRN-0001',
        'delivery_date' => now()->toDateString(),
        'status' => 'draft',
        'erp_shipment_reference' => 'ERP-1',
        'wms_shipment_reference' => 'WMS-1',
        'delivery_fee' => 12.5,
        'item_id' => [301, 302],
        'product_id' => [901, 902],
        'quantity_ordered' => [10, 20],
        'quantity_received' => [4, 8],
        'batch_number' => ['B-A', 'B-B'],
        'expiry_date' => [null, null],
        'picking_rule_applied' => ['FIFO', 'FEFO'],
    ], 10);

    expect($delivery)->toBeInstanceOf(Grn::class);
    expect((int) $delivery->company_id)->toBe(10);
    expect($delivery->grn_number)->toBe('GRN-0001');
    expect(DB::table('grn_items')->where('grn_id', $delivery->id)->count())->toBe(2);
});

it('updates grn and replaces items via service', function () {
    $service = app(GrnService::class);
    $delivery = $service->create([
        'purchase_order_id' => 7002,
        'warehouse_id' => 6,
        'type' => 'inbound',
        'delivery_number' => 'GRN-0002',
        'delivery_date' => now()->toDateString(),
        'status' => 'draft',
        'delivery_fee' => null,
        'item_id' => [401],
        'product_id' => [903],
        'quantity_ordered' => [10],
        'quantity_received' => [1],
        'batch_number' => ['OLD'],
        'expiry_date' => [null],
        'picking_rule_applied' => ['FIFO'],
    ], 10);

    $updated = $service->update($delivery->fresh(), [
        'purchase_order_id' => 7002,
        'warehouse_id' => 8,
        'type' => 'inbound',
        'delivery_number' => 'GRN-0002',
        'delivery_date' => now()->toDateString(),
        'status' => 'inbound',
        'delivery_fee' => 33.3,
        'item_id' => [402, 403],
        'product_id' => [904, 905],
        'quantity_ordered' => [12, 13],
        'quantity_received' => [3, 4],
        'batch_number' => ['NEW-1', 'NEW-2'],
        'expiry_date' => [null, null],
        'picking_rule_applied' => ['FIFO', 'FEFO'],
    ]);

    expect((int) $updated->warehouse_id)->toBe(8);
    expect($updated->status)->toBe('inbound');
    expect(DB::table('grn_items')->where('grn_id', $updated->id)->count())->toBe(2);
    expect(DB::table('grn_items')->where('grn_id', $updated->id)->where('batch_number', 'OLD')->count())->toBe(0);
});
