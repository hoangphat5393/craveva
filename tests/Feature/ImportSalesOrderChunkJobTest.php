<?php

use App\Jobs\ImportSalesOrderChunkJob;
use App\Models\Order;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Order::flushEventListeners();

    Schema::create('orders', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('client_id')->nullable();
        $table->string('order_number')->nullable();
        $table->string('custom_order_number')->nullable();
        $table->string('original_order_number')->nullable();
        $table->date('order_date')->nullable();
        $table->decimal('sub_total', 30, 2)->nullable();
        $table->decimal('total', 30, 2)->nullable();
        $table->decimal('discount', 30, 2)->default(0);
        $table->string('discount_type')->nullable();
        $table->string('status')->nullable();
        $table->unsignedInteger('currency_id')->nullable();
        $table->string('show_shipping_address')->default('no');
        $table->unsignedBigInteger('company_address_id')->nullable();
        $table->text('note')->nullable();
        $table->timestamps();
    });

    Schema::create('order_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('order_id');
        $table->unsignedBigInteger('product_id')->nullable();
        $table->string('item_name');
        $table->string('item_summary')->nullable();
        $table->string('type')->nullable();
        $table->decimal('quantity', 30, 2)->default(0);
        $table->decimal('unit_price', 30, 2)->default(0);
        $table->decimal('amount', 30, 2)->default(0);
        $table->unsignedBigInteger('unit_id')->nullable();
        $table->string('sku')->nullable();
        $table->integer('field_order')->nullable();
        $table->timestamps();
    });

    Schema::create('products', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->string('name')->nullable();
        $table->string('sku')->nullable();
        $table->unsignedBigInteger('unit_id')->nullable();
        $table->timestamps();
    });

    Schema::create('client_details', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('user_id');
        $table->string('client_code')->nullable();
        $table->timestamps();
    });

    Schema::create('company_addresses', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->boolean('is_default')->default(false);
        $table->timestamps();
    });

    Schema::create('unit_types', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->string('unit_type')->nullable();
        $table->timestamps();
    });

    Schema::create('order_import_rows', function ($table) {
        $table->id();
        $table->unsignedBigInteger('company_id');
        $table->unsignedBigInteger('order_id')->nullable();
        $table->unsignedBigInteger('order_item_id')->nullable();
        $table->string('source_hash', 64);
        $table->date('shipment_date')->nullable();
        $table->string('customer_code', 191)->nullable();
        $table->string('product_sku', 191)->nullable();
        $table->decimal('net_sales_volume', 30, 6)->nullable();
        $table->decimal('net_sales_amount', 30, 6)->nullable();
        $table->timestamps();
    });
});

it('imports sales order row and is idempotent by source hash', function () {
    DB::table('client_details')->insert([
        'company_id' => 1,
        'user_id' => 101,
        'client_code' => 'A1173',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('products')->insert([
        'company_id' => 1,
        'name' => 'Product A',
        'sku' => 'A0101009',
        'unit_id' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('company_addresses')->insert([
        ['company_id' => 2, 'is_default' => true, 'created_at' => now(), 'updated_at' => now()],
        ['company_id' => 1, 'is_default' => true, 'created_at' => now(), 'updated_at' => now()],
    ]);
    DB::table('unit_types')->insert([
        ['company_id' => 2, 'unit_type' => 'wrong-tenant', 'created_at' => now(), 'updated_at' => now()],
        ['company_id' => 1, 'unit_type' => 'piece', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $rows = [
        ['2024/01/02', 'A1173', 'A0101009', '2', '4100'],
    ];
    $columns = ['shipment_return_date', 'customer_number', 'product_part_number', 'net_sales_volume', 'net_sales_amount'];
    $company = (object) ['id' => 1, 'currency_id' => 1];

    (new ImportSalesOrderChunkJob($rows, $columns, $company, 0))->handle();
    (new ImportSalesOrderChunkJob($rows, $columns, $company, 0))->handle();

    expect(DB::table('orders')->count())->toBe(1);
    expect(DB::table('order_items')->count())->toBe(1);
    expect(DB::table('order_import_rows')->count())->toBe(1);
    expect((int) DB::table('orders')->value('company_address_id'))->toBe(2);
    expect((int) DB::table('order_items')->value('unit_id'))->toBe(2);
});

it('fails row when client or product not found', function () {
    $rows = [
        ['2024/01/02', 'NOCLIENT', 'NOSKU', '2', '4100'],
    ];
    $columns = ['shipment_return_date', 'customer_number', 'product_part_number', 'net_sales_volume', 'net_sales_amount'];
    $company = (object) ['id' => 1, 'currency_id' => 1];

    $job = new ImportSalesOrderChunkJob($rows, $columns, $company, 0);

    try {
        $job->handle();
    } catch (Throwable $e) {
        // Ignore fail() side effects in test environment.
    }

    expect(DB::table('orders')->count())->toBe(0);
    expect(DB::table('order_import_rows')->count())->toBe(0);
});
