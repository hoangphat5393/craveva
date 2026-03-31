<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('sales_shipment_items');
    Schema::dropIfExists('sales_shipments');
});

afterEach(function () {
    Schema::dropIfExists('sales_shipment_items');
    Schema::dropIfExists('sales_shipments');

    $baseline = base_path('storage/app/reports/sales-do-baseline-test.json');
    $output = base_path('storage/app/reports/sales-do-reconcile-test.json');
    if (file_exists($baseline)) {
        unlink($baseline);
    }
    if (file_exists($output)) {
        unlink($output);
    }
});

it('fails when baseline option is missing', function () {
    $this->artisan('purchase:sales-do-reconcile-report')
        ->expectsOutput('Missing required option: --baseline=<path-to-baseline-json>.')
        ->assertExitCode(1);
});

it('fails when baseline file is not found', function () {
    $this->artisan('purchase:sales-do-reconcile-report', [
        '--baseline' => 'storage/app/reports/non-exist.json',
    ])->assertExitCode(1);
});

it('generates reconciliation report from baseline and current snapshot', function () {
    Schema::create('sales_shipments', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('order_id');
        $table->unsignedBigInteger('warehouse_id');
        $table->string('shipment_number')->nullable();
        $table->date('shipment_date')->nullable();
        $table->string('status')->default('draft');
        $table->boolean('outbound_stock_applied')->default(false);
        $table->timestamps();
    });

    Schema::create('sales_shipment_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('sales_shipment_id');
        $table->unsignedBigInteger('order_item_id')->nullable();
        $table->unsignedBigInteger('product_id')->nullable();
        $table->decimal('quantity_shipped', 20, 4)->default(0);
        $table->timestamps();
    });

    $shipmentId = DB::table('sales_shipments')->insertGetId([
        'company_id' => 10,
        'order_id' => 1001,
        'warehouse_id' => 1,
        'shipment_number' => 'SS-BL-001',
        'shipment_date' => now()->toDateString(),
        'status' => 'confirmed',
        'outbound_stock_applied' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('sales_shipment_items')->insert([
        'sales_shipment_id' => $shipmentId,
        'order_item_id' => 501,
        'product_id' => 99,
        'quantity_shipped' => 2,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $baselinePath = base_path('storage/app/reports/sales-do-baseline-test.json');
    $reconcilePath = base_path('storage/app/reports/sales-do-reconcile-test.json');
    if (! is_dir(dirname($baselinePath))) {
        mkdir(dirname($baselinePath), 0777, true);
    }
    file_put_contents($baselinePath, json_encode([
        'source' => [
            'shipments_count' => 1,
            'items_count' => 1,
            'status_distribution' => ['confirmed' => 1],
            'outbound_stock_applied_count' => 1,
            'total_quantity_shipped' => 1.5,
        ],
    ], JSON_PRETTY_PRINT));

    DB::table('sales_shipment_items')->insert([
        'sales_shipment_id' => $shipmentId,
        'order_item_id' => 502,
        'product_id' => 100,
        'quantity_shipped' => 0.5,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->artisan('purchase:sales-do-reconcile-report', [
        '--baseline' => 'storage/app/reports/sales-do-baseline-test.json',
        '--company_id' => 10,
        '--output' => 'storage/app/reports/sales-do-reconcile-test.json',
    ])->assertExitCode(0);

    expect(file_exists($reconcilePath))->toBeTrue();
    $decoded = json_decode((string) file_get_contents($reconcilePath), true);
    expect($decoded)->toBeArray();
    expect((int) data_get($decoded, 'comparison.shipments_count_delta'))->toBe(0);
    expect((int) data_get($decoded, 'comparison.items_count_delta'))->toBe(1);
    expect((float) data_get($decoded, 'comparison.total_quantity_shipped_delta'))->toBe(1.0);
});
