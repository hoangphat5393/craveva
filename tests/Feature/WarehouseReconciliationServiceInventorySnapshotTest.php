<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Warehouse\Services\WarehouseReconciliationService;

beforeEach(function (): void {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    foreach (
        [
            'warehouse_product_stock',
            'warehouse_product_batches',
            'products',
            'warehouses',
        ] as $table
    ) {
        Schema::dropIfExists($table);
    }

    Schema::create('warehouses', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('name')->nullable();
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
    });

    DB::table('warehouses')->insert([
        'id' => 1,
        'company_id' => 1,
        'name' => 'Main',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('products')->insert([
        'id' => 1,
        'company_id' => 1,
        'name' => 'SKU A',
        'type' => 'goods',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

it('reports in sync when snapshot matches batch sum within epsilon', function (): void {
    DB::table('warehouse_product_stock')->insert([
        'warehouse_id' => 1,
        'product_id' => 1,
        'quantity' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('warehouse_product_batches')->insert([
        'company_id' => 1,
        'warehouse_id' => 1,
        'product_id' => 1,
        'quantity' => 10,
        'reserved_quantity' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $result = app(WarehouseReconciliationService::class)->inventorySnapshotVsBatchTotals(1);

    expect($result['mismatch_count'])->toBe(0)
        ->and($result['significant_mismatch_count'])->toBe(0)
        ->and($result['samples'])->toBeArray()->toBeEmpty();
});

it('classifies small delta as mismatch but not significant', function (): void {
    Config::set('warehouse.inventory_reconciliation.equality_epsilon', 0.0001);
    Config::set('warehouse.inventory_reconciliation.warning_absolute_delta', 0.01);

    DB::table('warehouse_product_stock')->insert([
        'warehouse_id' => 1,
        'product_id' => 1,
        'quantity' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('warehouse_product_batches')->insert([
        'company_id' => 1,
        'warehouse_id' => 1,
        'product_id' => 1,
        'quantity' => 9.99985,
        'reserved_quantity' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $result = app(WarehouseReconciliationService::class)->inventorySnapshotVsBatchTotals(1);

    expect($result['mismatch_count'])->toBe(1)
        ->and($result['significant_mismatch_count'])->toBe(0)
        ->and($result['equality_epsilon'])->toBe(0.0001)
        ->and($result['warning_absolute_delta'])->toBe(0.01);

    $sample = $result['samples'][0];
    expect($sample['is_significant'])->toBeFalse()
        ->and((float) $sample['delta'])->toBeGreaterThan(0.0001);
});

it('classifies large delta as significant mismatch', function (): void {
    DB::table('warehouse_product_stock')->insert([
        'warehouse_id' => 1,
        'product_id' => 1,
        'quantity' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('warehouse_product_batches')->insert([
        'company_id' => 1,
        'warehouse_id' => 1,
        'product_id' => 1,
        'quantity' => 4,
        'reserved_quantity' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $result = app(WarehouseReconciliationService::class)->inventorySnapshotVsBatchTotals(1);

    expect($result['mismatch_count'])->toBe(1)
        ->and($result['significant_mismatch_count'])->toBe(1)
        ->and($result['samples'][0]['is_significant'])->toBeTrue();
});

it('respects custom equality epsilon from config', function (): void {
    Config::set('warehouse.inventory_reconciliation.equality_epsilon', 0.5);
    Config::set('warehouse.inventory_reconciliation.warning_absolute_delta', 1.0);

    DB::table('warehouse_product_stock')->insert([
        'warehouse_id' => 1,
        'product_id' => 1,
        'quantity' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('warehouse_product_batches')->insert([
        'company_id' => 1,
        'warehouse_id' => 1,
        'product_id' => 1,
        'quantity' => 10.2,
        'reserved_quantity' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $result = app(WarehouseReconciliationService::class)->inventorySnapshotVsBatchTotals(1);

    expect($result['mismatch_count'])->toBe(0)
        ->and($result['equality_epsilon'])->toBe(0.5);
});
