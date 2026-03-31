<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('sales_do_items');
    Schema::dropIfExists('sales_dos');
});

afterEach(function () {
    Schema::dropIfExists('sales_do_items');
    Schema::dropIfExists('sales_dos');

    $manifest = base_path('storage/app/reports/sales-do-migrate-manifest-test.json');
    if (file_exists($manifest)) {
        unlink($manifest);
    }
});

it('fails when manifest option is missing', function () {
    Schema::create('sales_dos', function ($table) {
        $table->id();
        $table->unsignedBigInteger('legacy_sales_shipment_id')->nullable()->unique();
    });
    Schema::create('sales_do_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('sales_do_id');
        $table->unsignedBigInteger('legacy_sales_shipment_item_id')->nullable()->unique();
    });

    $this->artisan('purchase:sales-do-migrate-rollback')
        ->expectsOutput('Missing required option: --manifest=<path-to-manifest-json>.')
        ->assertExitCode(1);
});

it('deletes migrated rows from manifest in execute mode', function () {
    Schema::create('sales_dos', function ($table) {
        $table->id();
        $table->unsignedBigInteger('legacy_sales_shipment_id')->nullable()->unique();
    });
    Schema::create('sales_do_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('sales_do_id');
        $table->unsignedBigInteger('legacy_sales_shipment_item_id')->nullable()->unique();
    });

    $headerId = DB::table('sales_dos')->insertGetId([
        'legacy_sales_shipment_id' => 11,
    ]);
    $itemIdA = DB::table('sales_do_items')->insertGetId([
        'sales_do_id' => $headerId,
        'legacy_sales_shipment_item_id' => 101,
    ]);
    $itemIdB = DB::table('sales_do_items')->insertGetId([
        'sales_do_id' => $headerId,
        'legacy_sales_shipment_item_id' => 102,
    ]);

    $manifestPath = base_path('storage/app/reports/sales-do-migrate-manifest-test.json');
    if (! is_dir(dirname($manifestPath))) {
        mkdir(dirname($manifestPath), 0777, true);
    }
    file_put_contents($manifestPath, json_encode([
        'created_header_ids' => [$headerId],
        'created_item_ids' => [$itemIdA, $itemIdB],
    ], JSON_PRETTY_PRINT));

    $this->artisan('purchase:sales-do-migrate-rollback', [
        '--manifest' => 'storage/app/reports/sales-do-migrate-manifest-test.json',
    ])->assertExitCode(0);

    expect(DB::table('sales_dos')->count())->toBe(1);
    expect(DB::table('sales_do_items')->count())->toBe(2);

    $this->artisan('purchase:sales-do-migrate-rollback', [
        '--manifest' => 'storage/app/reports/sales-do-migrate-manifest-test.json',
        '--execute' => true,
        '--force' => true,
    ])->assertExitCode(0);

    expect(DB::table('sales_do_items')->count())->toBe(0);
    expect(DB::table('sales_dos')->count())->toBe(0);
});
