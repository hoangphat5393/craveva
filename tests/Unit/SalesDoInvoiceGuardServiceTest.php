<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Services\SalesDoInvoiceGuardService;

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::create('sales_dos', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->unsignedBigInteger('order_id');
        $table->string('status');
        $table->timestamps();
    });

    Schema::create('sales_do_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('sales_do_id');
        $table->unsignedInteger('product_id')->nullable();
        $table->decimal('quantity_shipped', 20, 4)->default(0);
        $table->timestamps();
    });

    Schema::create('invoices', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->unsignedBigInteger('order_id')->nullable();
        $table->string('status')->default('unpaid');
        $table->timestamps();
    });

    Schema::create('invoice_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('invoice_id');
        $table->unsignedInteger('product_id')->nullable();
        $table->decimal('quantity', 20, 4)->default(0);
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('invoice_items');
    Schema::dropIfExists('invoices');
    Schema::dropIfExists('sales_shipment_items');
    Schema::dropIfExists('sales_shipments');
    Schema::dropIfExists('sales_do_items');
    Schema::dropIfExists('sales_dos');
});

it('uses legacy sales shipment tables when sales do tables are not present', function () {
    Schema::dropIfExists('sales_do_items');
    Schema::dropIfExists('sales_dos');

    Schema::create('sales_shipments', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->unsignedBigInteger('order_id');
        $table->string('status');
        $table->timestamps();
    });

    Schema::create('sales_shipment_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('sales_shipment_id');
        $table->unsignedInteger('product_id')->nullable();
        $table->decimal('quantity_shipped', 20, 4)->default(0);
        $table->timestamps();
    });

    DB::table('sales_shipments')->insert([
        'id' => 1,
        'company_id' => 10,
        'order_id' => 100,
        'status' => 'shipped',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('sales_shipment_items')->insert([
        'sales_shipment_id' => 1,
        'product_id' => 200,
        'quantity_shipped' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $exceeded = app(SalesDoInvoiceGuardService::class)->exceededProducts(
        companyId: 10,
        orderId: 100,
        productIds: [200],
        quantities: [2],
    );

    expect($exceeded)->toHaveCount(1)
        ->and($exceeded[0]['shipped'])->toBe(1.0);
});

it('does not fail when sales delivery tables are not installed yet', function () {
    Schema::dropIfExists('sales_do_items');
    Schema::dropIfExists('sales_dos');

    $exceeded = app(SalesDoInvoiceGuardService::class)->exceededProducts(
        companyId: 10,
        orderId: 100,
        productIds: [200],
        quantities: [2],
    );

    expect($exceeded)->toBe([]);
});

it('blocks invoice quantities above shipped and uninvoiced sales delivery quantity', function () {
    DB::table('sales_dos')->insert([
        'id' => 1,
        'company_id' => 10,
        'order_id' => 100,
        'status' => 'shipped',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('sales_do_items')->insert([
        'sales_do_id' => 1,
        'product_id' => 200,
        'quantity_shipped' => 5,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('invoices')->insert([
        'id' => 9,
        'company_id' => 10,
        'order_id' => 100,
        'status' => 'unpaid',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('invoice_items')->insert([
        'invoice_id' => 9,
        'product_id' => 200,
        'quantity' => 4,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $exceeded = app(SalesDoInvoiceGuardService::class)->exceededProducts(
        companyId: 10,
        orderId: 100,
        productIds: [200],
        quantities: [2],
    );

    expect($exceeded)->toHaveCount(1)
        ->and($exceeded[0]['product_id'])->toBe(200)
        ->and($exceeded[0]['already_invoiced'])->toBe(4.0)
        ->and($exceeded[0]['shipped'])->toBe(5.0);
});

it('allows invoice quantities within remaining shipped sales delivery quantity', function () {
    DB::table('sales_dos')->insert([
        'id' => 1,
        'company_id' => 10,
        'order_id' => 100,
        'status' => 'delivered',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('sales_do_items')->insert([
        'sales_do_id' => 1,
        'product_id' => 200,
        'quantity_shipped' => 5,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('invoices')->insert([
        'id' => 9,
        'company_id' => 10,
        'order_id' => 100,
        'status' => 'unpaid',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('invoice_items')->insert([
        'invoice_id' => 9,
        'product_id' => 200,
        'quantity' => 4,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $exceeded = app(SalesDoInvoiceGuardService::class)->exceededProducts(
        companyId: 10,
        orderId: 100,
        productIds: [200],
        quantities: [1],
    );

    expect($exceeded)->toBe([]);
});
