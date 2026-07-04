<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Entities\PurchaseProduct;
use Modules\Purchase\Services\InventoryImportRowProcessor;

beforeEach(function (): void {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    PurchaseProduct::flushEventListeners();

    Schema::create('products', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('name');
        $table->string('sku')->nullable();
        $table->decimal('price', 15, 4)->default(0);
        $table->decimal('purchase_price', 15, 4)->default(0);
        $table->boolean('track_inventory')->default(false);
        $table->unsignedBigInteger('unit_id')->nullable();
        $table->text('description')->nullable();
        $table->timestamps();
    });

    Schema::create('unit_types', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('unit_type');
        $table->boolean('default')->default(false);
        $table->timestamps();
    });
});

it('rolls back product and unit writes when a later inventory write fails', function (): void {
    $processor = new InventoryImportRowProcessor(
        ['New Product', 'NEW-001', 'piece', 10],
        ['product_name', 'sku', 'unit', 'quantity'],
        (object) ['id' => 1],
    );

    expect(fn () => $processor->run())->toThrow(RuntimeException::class);
    expect(DB::table('products')->count())->toBe(0);
    expect(DB::table('unit_types')->count())->toBe(0);
});
