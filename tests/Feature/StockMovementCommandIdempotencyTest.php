<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Warehouse\Services\StockMovementService;

beforeEach(function (): void {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    Config::set('warehouse.allow_negative_stock', false);
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::create('warehouses', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('name')->nullable();
        $table->string('warehouse_type')->default('normal');
        $table->string('status')->default('active');
        $table->timestamps();
    });

    Schema::create('products', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('name')->nullable();
        $table->string('type')->default('goods');
        $table->timestamps();
    });

    Schema::create('warehouse_product_batches', function ($table): void {
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

    Schema::create('warehouse_product_stock', function ($table): void {
        $table->id();
        $table->unsignedBigInteger('warehouse_id');
        $table->unsignedBigInteger('product_id');
        $table->decimal('quantity', 20, 4)->default(0);
        $table->timestamps();
        $table->unique(['warehouse_id', 'product_id']);
    });

    Schema::create('stock_movements', function ($table): void {
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
        $table->string('idempotency_key', 100)->nullable();
        $table->timestamps();
    });

    Schema::create('stock_movement_commands', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('movement_type', 32);
        $table->string('idempotency_key', 100);
        $table->timestamps();
        $table->unique(['company_id', 'movement_type', 'idempotency_key']);
    });

    DB::table('warehouses')->insert([
        'id' => 1,
        'company_id' => 10,
        'name' => 'Main',
        'warehouse_type' => 'normal',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('products')->insert([
        'id' => 99,
        'company_id' => 10,
        'name' => 'Material',
        'type' => 'goods',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

afterEach(function (): void {
    Schema::dropIfExists('stock_movement_commands');
    Schema::dropIfExists('stock_movements');
    Schema::dropIfExists('warehouse_product_stock');
    Schema::dropIfExists('warehouse_product_batches');
    Schema::dropIfExists('products');
    Schema::dropIfExists('warehouses');
});

it('does not apply the same inbound command twice', function (): void {
    $payload = [
        'company_id' => 10,
        'warehouse_id' => 1,
        'product_id' => 99,
        'quantity' => 5,
        'reference_type' => 'test-inbound',
        'reference_id' => 101,
        'idempotency_key' => 'test-inbound:101',
    ];

    $service = app(StockMovementService::class);
    $service->recordInbound($payload);
    $service->recordInbound($payload);

    expect((float) DB::table('warehouse_product_batches')->value('quantity'))->toBe(5.0)
        ->and((float) DB::table('warehouse_product_stock')->value('quantity'))->toBe(5.0)
        ->and(DB::table('stock_movements')->count())->toBe(1)
        ->and(DB::table('stock_movement_commands')->count())->toBe(1);
});

it('does not apply the same outbound command twice', function (): void {
    DB::table('warehouse_product_batches')->insert([
        'company_id' => 10,
        'warehouse_id' => 1,
        'product_id' => 99,
        'batch_number' => 'RM-1',
        'quantity' => 10,
        'reserved_quantity' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $payload = [
        'company_id' => 10,
        'warehouse_id' => 1,
        'product_id' => 99,
        'quantity' => 3,
        'batch_number' => 'RM-1',
        'reference_type' => 'test-outbound',
        'reference_id' => 202,
        'idempotency_key' => 'test-outbound:202',
    ];

    $service = app(StockMovementService::class);
    $service->recordOutbound($payload);
    $service->recordOutbound($payload);

    expect((float) DB::table('warehouse_product_batches')->value('quantity'))->toBe(7.0)
        ->and((float) DB::table('warehouse_product_stock')->value('quantity'))->toBe(7.0)
        ->and(DB::table('stock_movements')->count())->toBe(1)
        ->and(DB::table('stock_movement_commands')->count())->toBe(1);
});

it('records every allocation line while keeping the outbound command idempotent', function (): void {
    DB::table('warehouse_product_batches')->insert([
        [
            'company_id' => 10,
            'warehouse_id' => 1,
            'product_id' => 99,
            'batch_number' => 'RM-A',
            'quantity' => 2,
            'reserved_quantity' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'company_id' => 10,
            'warehouse_id' => 1,
            'product_id' => 99,
            'batch_number' => 'RM-B',
            'quantity' => 3,
            'reserved_quantity' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $payload = [
        'company_id' => 10,
        'warehouse_id' => 1,
        'product_id' => 99,
        'quantity' => 4,
        'reference_type' => 'test-fefo-outbound',
        'reference_id' => 303,
        'idempotency_key' => 'test-fefo-outbound:303',
    ];

    $service = app(StockMovementService::class);
    $service->recordOutbound($payload);
    $service->recordOutbound($payload);

    expect((float) DB::table('warehouse_product_batches')->sum('quantity'))->toBe(1.0)
        ->and((float) DB::table('stock_movements')->sum('quantity'))->toBe(4.0)
        ->and(DB::table('stock_movements')->count())->toBe(2)
        ->and(DB::table('stock_movement_commands')->count())->toBe(1);
});
