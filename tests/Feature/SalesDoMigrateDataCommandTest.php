<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('sales_do_items');
    Schema::dropIfExists('sales_dos');
    Schema::dropIfExists('sales_shipment_items');
    Schema::dropIfExists('sales_shipments');
});

afterEach(function () {
    Schema::dropIfExists('sales_do_items');
    Schema::dropIfExists('sales_dos');
    Schema::dropIfExists('sales_shipment_items');
    Schema::dropIfExists('sales_shipments');

    $reportPath = base_path('storage/app/reports/sales-do-migrate-test.json');
    $reportPath2 = base_path('storage/app/reports/sales-do-migrate-test-2.json');
    $manifestPath = base_path('storage/app/reports/sales-do-migrate-manifest-test.json');
    foreach ([$reportPath, $reportPath2, $manifestPath] as $path) {
        if (file_exists($path)) {
            unlink($path);
        }
    }
});

it('fails when required target tables are missing', function () {
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
        $table->decimal('quantity_ordered', 20, 4)->default(0);
        $table->decimal('quantity_shipped', 20, 4)->default(0);
        $table->timestamps();
    });

    $this->artisan('purchase:sales-do-migrate-data')
        ->expectsOutputToContain('Required tables not found:')
        ->assertExitCode(1);
});

it('migrates source rows into sales do tables in execute mode and stays idempotent', function () {
    Schema::create('sales_shipments', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('order_id');
        $table->unsignedBigInteger('warehouse_id');
        $table->string('shipment_number')->nullable();
        $table->date('shipment_date')->nullable();
        $table->string('status')->default('draft');
        $table->boolean('outbound_stock_applied')->default(false);
        $table->text('notes')->nullable();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->timestamps();
    });
    Schema::create('sales_shipment_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('sales_shipment_id');
        $table->unsignedBigInteger('order_item_id');
        $table->unsignedInteger('product_id')->nullable();
        $table->decimal('quantity_ordered', 20, 4)->default(0);
        $table->decimal('quantity_shipped', 20, 4)->default(0);
        $table->unsignedBigInteger('unit_id')->nullable();
        $table->string('batch_number')->nullable();
        $table->timestamps();
    });
    Schema::create('sales_dos', function ($table) {
        $table->id();
        $table->unsignedBigInteger('legacy_sales_shipment_id')->nullable()->unique();
        $table->unsignedInteger('company_id');
        $table->unsignedBigInteger('order_id');
        $table->unsignedBigInteger('warehouse_id');
        $table->string('do_number')->nullable();
        $table->date('do_date')->nullable();
        $table->string('status')->default('draft');
        $table->boolean('outbound_stock_applied')->default(false);
        $table->text('notes')->nullable();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->timestamps();
    });
    Schema::create('sales_do_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('sales_do_id');
        $table->unsignedBigInteger('legacy_sales_shipment_item_id')->nullable()->unique();
        $table->unsignedBigInteger('order_item_id');
        $table->unsignedInteger('product_id')->nullable();
        $table->decimal('quantity_ordered', 20, 4)->default(0);
        $table->decimal('quantity_shipped', 20, 4)->default(0);
        $table->unsignedBigInteger('unit_id')->nullable();
        $table->string('batch_number')->nullable();
        $table->timestamps();
    });

    $shipmentId = DB::table('sales_shipments')->insertGetId([
        'company_id' => 10,
        'order_id' => 1001,
        'warehouse_id' => 1,
        'shipment_number' => 'SS-MG-001',
        'shipment_date' => now()->toDateString(),
        'status' => 'confirmed',
        'outbound_stock_applied' => 1,
        'notes' => 'test',
        'created_by' => 1,
        'updated_by' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('sales_shipment_items')->insert([
        [
            'sales_shipment_id' => $shipmentId,
            'order_item_id' => 501,
            'product_id' => 99,
            'quantity_ordered' => 5,
            'quantity_shipped' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'sales_shipment_id' => $shipmentId,
            'order_item_id' => 502,
            'product_id' => 100,
            'quantity_ordered' => 7,
            'quantity_shipped' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $report1 = 'storage/app/reports/sales-do-migrate-test.json';
    $report2 = 'storage/app/reports/sales-do-migrate-test-2.json';
    $this->artisan('purchase:sales-do-migrate-data', [
        '--company_id' => 10,
        '--execute' => true,
        '--force' => true,
        '--output' => $report1,
    ])->assertExitCode(0);

    expect(DB::table('sales_dos')->count())->toBe(1);
    expect(DB::table('sales_do_items')->count())->toBe(2);

    $decoded1 = json_decode((string) file_get_contents(base_path($report1)), true);
    expect((int) data_get($decoded1, 'execute_result.created_headers_count'))->toBe(1);
    expect((int) data_get($decoded1, 'execute_result.created_items_count'))->toBe(2);

    $this->artisan('purchase:sales-do-migrate-data', [
        '--company_id' => 10,
        '--execute' => true,
        '--force' => true,
        '--output' => $report2,
    ])->assertExitCode(0);

    expect(DB::table('sales_dos')->count())->toBe(1);
    expect(DB::table('sales_do_items')->count())->toBe(2);

    $decoded2 = json_decode((string) file_get_contents(base_path($report2)), true);
    expect((int) data_get($decoded2, 'execute_result.created_headers_count'))->toBe(0);
    expect((int) data_get($decoded2, 'execute_result.created_items_count'))->toBe(0);
});
