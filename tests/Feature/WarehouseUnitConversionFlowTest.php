<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Warehouse\Services\StockMovementService;
use Modules\Warehouse\Services\StockReservationService;

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    Config::set('warehouse.strict_unit_conversion', true);
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::create('unit_types', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->string('unit_type')->nullable();
        $table->timestamps();
    });

    Schema::create('products', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->string('name')->nullable();
        $table->unsignedBigInteger('unit_id')->nullable();
        $table->string('type')->default('goods');
        $table->timestamps();
    });

    Schema::create('warehouses', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->string('name')->nullable();
        $table->string('warehouse_type')->default('normal');
        $table->enum('status', ['active', 'inactive'])->default('active');
        $table->timestamps();
    });

    Schema::create('warehouse_product_batches', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('warehouse_id');
        $table->unsignedBigInteger('product_id');
        $table->string('batch_number')->nullable();
        $table->date('expiration_date')->nullable();
        $table->decimal('quantity', 20, 4)->default(0);
        $table->decimal('reserved_quantity', 20, 4)->default(0);
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
        $table->unsignedBigInteger('unit_id')->nullable();
        $table->string('fefo_fifo_rule')->nullable();
        $table->string('reference_type')->nullable();
        $table->unsignedBigInteger('reference_id')->nullable();
        $table->string('idempotency_key')->nullable();
        $table->timestamps();
    });

    Schema::create('product_unit_conversions', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedInteger('product_id');
        $table->unsignedBigInteger('unit_id');
        $table->decimal('factor_to_base', 20, 8)->default(1);
        $table->timestamps();
    });

    DB::table('unit_types')->insert([
        ['id' => 1, 'company_id' => 1, 'unit_type' => 'Pcs', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'company_id' => 1, 'unit_type' => 'Box', 'created_at' => now(), 'updated_at' => now()],
    ]);
    DB::table('products')->insert([
        'id' => 100,
        'company_id' => 1,
        'name' => 'Product 100',
        'unit_id' => 1,
        'type' => 'goods',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('warehouses')->insert([
        'id' => 7,
        'company_id' => 1,
        'name' => 'WH-7',
        'warehouse_type' => 'normal',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('warehouse_product_batches')->insert([
        'company_id' => 1,
        'warehouse_id' => 7,
        'product_id' => 100,
        'batch_number' => null,
        'quantity' => 50,
        'reserved_quantity' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('product_unit_conversions')->insert([
        'company_id' => 1,
        'product_id' => 100,
        'unit_id' => 2,
        'factor_to_base' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

afterEach(function () {
    Schema::dropIfExists('product_unit_conversions');
    Schema::dropIfExists('stock_movements');
    Schema::dropIfExists('warehouse_product_stock');
    Schema::dropIfExists('stock_reservations');
    Schema::dropIfExists('warehouse_product_batches');
    Schema::dropIfExists('warehouses');
    Schema::dropIfExists('products');
    Schema::dropIfExists('unit_types');
});

it('converts reservation quantity from sales unit to base unit', function () {
    app(StockReservationService::class)->reserve([
        'company_id' => 1,
        'warehouse_id' => 7,
        'product_id' => 100,
        'quantity' => 2, // 2 box
        'unit_id' => 2,
        'batch_number' => null,
        'expiry_date' => null,
        'reference_type' => 'test',
        'reference_id' => 9001,
    ]);

    expect((float) DB::table('warehouse_product_batches')->where('warehouse_id', 7)->value('reserved_quantity'))
        ->toBe(20.0); // 20 pcs base
});

it('converts outbound movement quantity to base unit before deduct', function () {
    app(StockMovementService::class)->recordOutbound([
        'company_id' => 1,
        'warehouse_id' => 7,
        'product_id' => 100,
        'quantity' => 3, // 3 box
        'unit_id' => 2,
        'reference_type' => 'test',
        'reference_id' => 9002,
        'idempotency_key' => 'test-outbound-9002',
    ]);

    expect((float) DB::table('warehouse_product_batches')->where('warehouse_id', 7)->value('quantity'))
        ->toBe(20.0); // 50 - 30
});
