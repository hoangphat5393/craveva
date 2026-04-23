<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::create('orders', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('order_number');
        $table->timestamps();
    });

    Schema::create('order_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('order_id');
        $table->timestamps();
    });

    Schema::create('sales_dos', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('do_number');
        $table->timestamps();
    });

    Schema::create('sales_do_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('sales_do_id');
        $table->timestamps();
    });

    Schema::create('invoices', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('invoice_number');
        $table->timestamps();
    });

    Schema::create('invoice_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('invoice_id');
        $table->timestamps();
    });

    Schema::create('invoice_item_images', function ($table) {
        $table->id();
        $table->unsignedBigInteger('invoice_item_id');
        $table->timestamps();
    });

    Schema::create('payments', function ($table) {
        $table->id();
        $table->unsignedBigInteger('invoice_id')->nullable();
        $table->timestamps();
    });

    Schema::create('credit_notes', function ($table) {
        $table->id();
        $table->unsignedBigInteger('invoice_id')->nullable();
        $table->timestamps();
    });

    Schema::create('warehouse_product_batches', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->string('batch_number')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('warehouse_product_batches');
    Schema::dropIfExists('credit_notes');
    Schema::dropIfExists('payments');
    Schema::dropIfExists('invoice_item_images');
    Schema::dropIfExists('invoice_items');
    Schema::dropIfExists('invoices');
    Schema::dropIfExists('sales_do_items');
    Schema::dropIfExists('sales_dos');
    Schema::dropIfExists('order_items');
    Schema::dropIfExists('orders');
});

it('does not delete data in dry-run mode', function () {
    seedCleanupFixtures();

    $this->artisan('warehouse:demo-cleanup', [
        '--company_id' => 1,
        '--order_no' => 'ODR#004',
        '--do_no' => 'SS-000008',
        '--invoice_no' => 'INV#028',
        '--batch_no' => 'DEMO-ODR004-B1',
    ])->expectsOutputToContain('Dry-run only')
        ->assertExitCode(0);

    expect(DB::table('orders')->count())->toBe(2)
        ->and(DB::table('sales_dos')->count())->toBe(2)
        ->and(DB::table('invoices')->count())->toBe(2)
        ->and(DB::table('warehouse_product_batches')->count())->toBe(2);
});

it('deletes only targeted demo records in apply mode', function () {
    seedCleanupFixtures();

    $this->artisan('warehouse:demo-cleanup', [
        '--apply' => true,
        '--company_id' => 1,
        '--order_no' => 'ODR#004',
        '--do_no' => 'SS-000008',
        '--invoice_no' => 'INV#028',
        '--batch_no' => 'DEMO-ODR004-B1',
    ])->expectsOutputToContain('Demo cleanup completed successfully')
        ->assertExitCode(0);

    expect(DB::table('orders')->where('order_number', 'ODR#004')->exists())->toBeFalse()
        ->and(DB::table('sales_dos')->where('do_number', 'SS-000008')->exists())->toBeFalse()
        ->and(DB::table('invoices')->where('invoice_number', 'INV#028')->exists())->toBeFalse()
        ->and(DB::table('warehouse_product_batches')->where('batch_number', 'DEMO-ODR004-B1')->exists())->toBeFalse();

    expect(DB::table('orders')->where('order_number', 'ODR#999')->exists())->toBeTrue()
        ->and(DB::table('sales_dos')->where('do_number', 'SS-000999')->exists())->toBeTrue()
        ->and(DB::table('invoices')->where('invoice_number', 'INV#999')->exists())->toBeTrue()
        ->and(DB::table('warehouse_product_batches')->where('batch_number', 'DEMO-KEEP-B1')->exists())->toBeTrue();
});

function seedCleanupFixtures(): void
{
    DB::table('orders')->insert([
        ['id' => 12, 'company_id' => 1, 'order_number' => 'ODR#004', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 99, 'company_id' => 1, 'order_number' => 'ODR#999', 'created_at' => now(), 'updated_at' => now()],
    ]);

    DB::table('order_items')->insert([
        ['order_id' => 12, 'created_at' => now(), 'updated_at' => now()],
        ['order_id' => 99, 'created_at' => now(), 'updated_at' => now()],
    ]);

    DB::table('sales_dos')->insert([
        ['id' => 8, 'company_id' => 1, 'do_number' => 'SS-000008', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 999, 'company_id' => 1, 'do_number' => 'SS-000999', 'created_at' => now(), 'updated_at' => now()],
    ]);

    DB::table('sales_do_items')->insert([
        ['sales_do_id' => 8, 'created_at' => now(), 'updated_at' => now()],
        ['sales_do_id' => 999, 'created_at' => now(), 'updated_at' => now()],
    ]);

    DB::table('invoices')->insert([
        ['id' => 10, 'company_id' => 1, 'invoice_number' => 'INV#028', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 999, 'company_id' => 1, 'invoice_number' => 'INV#999', 'created_at' => now(), 'updated_at' => now()],
    ]);

    DB::table('invoice_items')->insert([
        ['id' => 100, 'invoice_id' => 10, 'created_at' => now(), 'updated_at' => now()],
        ['id' => 999, 'invoice_id' => 999, 'created_at' => now(), 'updated_at' => now()],
    ]);

    DB::table('invoice_item_images')->insert([
        ['invoice_item_id' => 100, 'created_at' => now(), 'updated_at' => now()],
        ['invoice_item_id' => 999, 'created_at' => now(), 'updated_at' => now()],
    ]);

    DB::table('payments')->insert([
        ['invoice_id' => 10, 'created_at' => now(), 'updated_at' => now()],
        ['invoice_id' => 999, 'created_at' => now(), 'updated_at' => now()],
    ]);

    DB::table('credit_notes')->insert([
        ['invoice_id' => 10, 'created_at' => now(), 'updated_at' => now()],
        ['invoice_id' => 999, 'created_at' => now(), 'updated_at' => now()],
    ]);

    DB::table('warehouse_product_batches')->insert([
        ['company_id' => 1, 'batch_number' => 'DEMO-ODR004-B1', 'created_at' => now(), 'updated_at' => now()],
        ['company_id' => 1, 'batch_number' => 'DEMO-KEEP-B1', 'created_at' => now(), 'updated_at' => now()],
    ]);
}
