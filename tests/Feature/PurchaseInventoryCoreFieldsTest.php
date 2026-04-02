<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Entities\PurchaseStockAdjustment;

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::create('purchase_stock_adjustments', function ($table) {
        $table->id();
        $table->unsignedBigInteger('warehouse_id')->nullable();
        $table->unsignedBigInteger('inventory_id')->nullable();
        $table->unsignedBigInteger('product_id')->nullable();
        $table->unsignedBigInteger('reason_id')->nullable();
        $table->string('type')->nullable();
        $table->date('date')->nullable();
        $table->string('reference_number')->nullable();
        $table->decimal('net_quantity', 15, 4)->nullable();
        $table->decimal('reserved_quantity', 15, 4)->nullable()->default(0);
        $table->decimal('quantity_adjustment', 15, 4)->nullable();
        $table->text('description')->nullable();
        $table->string('status')->nullable();
        $table->string('batch_number')->nullable();
        $table->date('manufacturing_date')->nullable();
        $table->date('expiration_date')->nullable();
        $table->decimal('changed_value', 15, 4)->nullable();
        $table->decimal('adjusted_value', 15, 4)->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('purchase_stock_adjustments');
});

it('exposes near_expiry_status from expiration_date', function () {
    Config::set('purchase.inventory_near_expiry_days', 30);

    $past = new PurchaseStockAdjustment;
    $past->expiration_date = now()->subDays(5)->toDateString();
    expect($past->near_expiry_status)->toBe('expired');

    $near = new PurchaseStockAdjustment;
    $near->expiration_date = now()->addDays(10)->toDateString();
    expect($near->near_expiry_status)->toBe('near_expiry');

    $ok = new PurchaseStockAdjustment;
    $ok->expiration_date = now()->addDays(60)->toDateString();
    expect($ok->near_expiry_status)->toBe('normal');

    $none = new PurchaseStockAdjustment;
    $none->expiration_date = null;
    expect($none->near_expiry_status)->toBeNull();
});

it('persists and reads reserved_quantity', function () {
    DB::table('purchase_stock_adjustments')->insert([
        'warehouse_id' => 1,
        'net_quantity' => 100,
        'reserved_quantity' => 12.5,
        'expiration_date' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $row = PurchaseStockAdjustment::first();
    expect((float) $row->reserved_quantity)->toBe(12.5);
    expect((float) $row->net_quantity)->toBe(100.0);
});
