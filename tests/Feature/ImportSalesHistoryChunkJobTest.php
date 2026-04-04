<?php

use App\Jobs\ImportSalesHistoryChunkJob;
use App\Models\SalesHistoryLine;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    SalesHistoryLine::flushEventListeners();

    Schema::create('sales_histories', function ($table) {
        $table->id();
        $table->unsignedBigInteger('company_id')->nullable();
        $table->string('source_filename')->nullable();
        $table->unsignedBigInteger('imported_by')->nullable();
        $table->timestamp('imported_at')->nullable();
        $table->timestamps();
    });

    Schema::create('sales_history_lines', function ($table) {
        $table->id();
        $table->unsignedBigInteger('company_id')->index();
        $table->unsignedBigInteger('sales_history_id')->nullable();
        $table->date('shipment_date');
        $table->unsignedBigInteger('client_id');
        $table->unsignedBigInteger('client_details_id')->nullable();
        $table->unsignedBigInteger('product_id');
        $table->decimal('quantity', 30, 6);
        $table->decimal('quantity_abs', 30, 6);
        $table->decimal('amount', 30, 6)->nullable();
        $table->decimal('unit_price', 30, 6)->nullable();
        $table->boolean('is_return')->default(false);
        $table->unsignedBigInteger('currency_id')->nullable();
        $table->string('source_sheet_name', 191)->nullable();
        $table->string('source_row_hash', 64);
        $table->decimal('net_sales_volume_raw', 30, 6)->nullable();
        $table->decimal('net_sales_amount_raw', 30, 6)->nullable();
        $table->timestamps();
        $table->unique(['company_id', 'source_row_hash'], 'sales_history_lines_company_hash_unique');
    });

    Schema::create('products', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->string('name')->nullable();
        $table->string('sku')->nullable();
        $table->timestamps();
    });

    Schema::create('client_details', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('user_id');
        $table->string('client_code')->nullable();
        $table->timestamps();
    });
});

it('imports sales history line and is idempotent by source hash', function () {
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
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $rows = [
        ['2024/01/02', 'A1173', 'A0101009', '2', '4100'],
    ];
    $columns = ['shipment_return_date', 'customer_number', 'product_part_number', 'net_sales_volume', 'net_sales_amount'];
    $company = (object) ['id' => 1, 'currency_id' => 1];
    $options = ['sales_history_id' => 1];

    (new ImportSalesHistoryChunkJob($rows, $columns, $company, 0, $options))->handle();
    (new ImportSalesHistoryChunkJob($rows, $columns, $company, 0, $options))->handle();

    expect(DB::table('sales_history_lines')->count())->toBe(1);
});

it('records no lines when client or product not found without throwing', function () {
    $rows = [
        ['2024/01/02', 'NOCLIENT', 'NOSKU', '2', '4100'],
    ];
    $columns = ['shipment_return_date', 'customer_number', 'product_part_number', 'net_sales_volume', 'net_sales_amount'];
    $company = (object) ['id' => 1, 'currency_id' => 1];
    $options = ['sales_history_id' => 1];

    (new ImportSalesHistoryChunkJob($rows, $columns, $company, 0, $options))->handle();

    expect(DB::table('sales_history_lines')->count())->toBe(0);
});

it('imports multiple data rows from first sheet only (source_sheet_name null)', function () {
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
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $rows = [
        ['2024/01/02', 'A1173', 'A0101009', '1', '100'],
        ['2024/01/03', 'A1173', 'A0101009', '2', '200'],
    ];
    $columns = ['shipment_return_date', 'customer_number', 'product_part_number', 'net_sales_volume', 'net_sales_amount'];
    $company = (object) ['id' => 1, 'currency_id' => 1];
    $options = ['sales_history_id' => 1];

    (new ImportSalesHistoryChunkJob($rows, $columns, $company, 0, $options))->handle();

    expect(DB::table('sales_history_lines')->count())->toBe(2);
    expect(DB::table('sales_history_lines')->whereNull('source_sheet_name')->count())->toBe(2);
});
