<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::create('warehouse_product_batches', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('warehouse_id');
        $table->unsignedInteger('product_id');
        $table->string('batch_number')->nullable();
        $table->date('expiration_date')->nullable();
        $table->date('manufacturing_date')->nullable();
        $table->decimal('quantity', 15, 4)->default(0);
        $table->decimal('reserved_quantity', 15, 4)->default(0);
        $table->timestamps();
    });

    Schema::create('sales_do_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('warehouse_batch_id')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('sales_do_items');
    Schema::dropIfExists('warehouse_product_batches');
});

it('keeps data unchanged in dry run mode', function () {
    DB::table('warehouse_product_batches')->insert([
        [
            'id' => 10,
            'company_id' => 1,
            'warehouse_id' => 7,
            'product_id' => 2,
            'batch_number' => 'B-100',
            'expiration_date' => '2026-12-31',
            'quantity' => 3,
            'reserved_quantity' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 11,
            'company_id' => 1,
            'warehouse_id' => 7,
            'product_id' => 2,
            'batch_number' => 'B-100',
            'expiration_date' => '2026-12-31',
            'quantity' => 5,
            'reserved_quantity' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $this->artisan('warehouse:batch-dedupe')
        ->expectsOutputToContain('Dry-run mode only')
        ->assertExitCode(0);

    expect(DB::table('warehouse_product_batches')->count())->toBe(2);
});

it('merges duplicate rows and repoints sales do items in apply mode', function () {
    DB::table('warehouse_product_batches')->insert([
        [
            'id' => 20,
            'company_id' => 1,
            'warehouse_id' => 8,
            'product_id' => 3,
            'batch_number' => 'B-200',
            'expiration_date' => '2027-01-31',
            'quantity' => 4,
            'reserved_quantity' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 21,
            'company_id' => 1,
            'warehouse_id' => 8,
            'product_id' => 3,
            'batch_number' => 'B-200',
            'expiration_date' => '2027-01-31',
            'quantity' => 6,
            'reserved_quantity' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    DB::table('sales_do_items')->insert([
        'warehouse_batch_id' => 21,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->artisan('warehouse:batch-dedupe --apply')
        ->expectsOutputToContain('Dedupe completed successfully')
        ->assertExitCode(0);

    expect(DB::table('warehouse_product_batches')->count())->toBe(1);

    $canonical = DB::table('warehouse_product_batches')->first();
    expect((int) $canonical->id)->toBe(20)
        ->and((float) $canonical->quantity)->toBe(10.0)
        ->and((float) $canonical->reserved_quantity)->toBe(3.0);

    $doItem = DB::table('sales_do_items')->first();
    expect((int) $doItem->warehouse_batch_id)->toBe(20);
});
