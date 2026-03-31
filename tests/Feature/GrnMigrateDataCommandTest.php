<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('grn_items');
    Schema::dropIfExists('grns');
    Schema::dropIfExists('delivery_order_items');
    Schema::dropIfExists('delivery_orders');
});

afterEach(function () {
    Schema::dropIfExists('grn_items');
    Schema::dropIfExists('grns');
    Schema::dropIfExists('delivery_order_items');
    Schema::dropIfExists('delivery_orders');
});

it('migrates delivery orders into grn tables in execute mode and remains idempotent', function () {
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
        $table->decimal('quantity_ordered', 20, 4)->default(0);
        $table->decimal('quantity_received', 20, 4)->default(0);
        $table->timestamps();
    });
    Schema::create('grns', function ($table) {
        $table->id();
        $table->unsignedBigInteger('legacy_delivery_order_id')->nullable()->unique();
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
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->timestamps();
    });
    Schema::create('grn_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('grn_id');
        $table->unsignedBigInteger('legacy_delivery_order_item_id')->nullable()->unique();
        $table->unsignedBigInteger('purchase_item_id')->nullable();
        $table->unsignedBigInteger('product_id')->nullable();
        $table->string('batch_number')->nullable();
        $table->date('expiry_date')->nullable();
        $table->string('picking_rule_applied')->nullable();
        $table->decimal('quantity_ordered', 20, 4)->default(0);
        $table->decimal('quantity_received', 20, 4)->default(0);
        $table->timestamps();
    });

    $headerId = DB::table('delivery_orders')->insertGetId([
        'company_id' => 10,
        'purchase_order_id' => 7001,
        'type' => 'inbound',
        'delivery_number' => 'GRN-001',
        'delivery_date' => now()->toDateString(),
        'warehouse_id' => 5,
        'status' => 'received',
        'inbound_stock_applied' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('delivery_order_items')->insert([
        'delivery_order_id' => $headerId,
        'purchase_item_id' => 301,
        'product_id' => 901,
        'quantity_ordered' => 10,
        'quantity_received' => 4,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->artisan('purchase:grn-migrate-data', [
        '--company_id' => 10,
        '--execute' => true,
        '--force' => true,
    ])->assertExitCode(0);

    expect(DB::table('grns')->count())->toBe(1);
    expect(DB::table('grn_items')->count())->toBe(1);

    $this->artisan('purchase:grn-migrate-data', [
        '--company_id' => 10,
        '--execute' => true,
        '--force' => true,
    ])->assertExitCode(0);

    expect(DB::table('grns')->count())->toBe(1);
    expect(DB::table('grn_items')->count())->toBe(1);
});
