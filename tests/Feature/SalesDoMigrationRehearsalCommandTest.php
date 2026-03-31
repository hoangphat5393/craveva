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
    $absoluteOutput = base_path('storage/app/reports/sales-do-rehearsal-test.json');
    if (file_exists($absoluteOutput)) {
        unlink($absoluteOutput);
    }
});

it('fails when required source tables are missing', function () {
    $this->artisan('purchase:sales-do-migration-rehearsal')
        ->expectsOutput('Required tables not found: sales_shipments and/or sales_shipment_items.')
        ->assertExitCode(1);
});

it('creates a dry-run json report file with source summary', function () {
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
        'shipment_number' => 'SS-DRY-001',
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
        'quantity_shipped' => 3.5,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $relativeOutput = 'storage/app/reports/sales-do-rehearsal-test.json';
    $absoluteOutput = base_path($relativeOutput);
    if (file_exists($absoluteOutput)) {
        unlink($absoluteOutput);
    }

    $this->artisan('purchase:sales-do-migration-rehearsal', [
        '--output' => $relativeOutput,
        '--company_id' => 10,
        '--sample' => 5,
    ])->assertExitCode(0);

    expect(file_exists($absoluteOutput))->toBeTrue();
    $content = file_get_contents($absoluteOutput);
    expect($content)->not->toBeFalse();
    $decoded = json_decode((string) $content, true);
    expect($decoded)->toBeArray();
    expect(data_get($decoded, 'mode'))->toBe('dry-run');
    expect((int) data_get($decoded, 'source.shipments_count'))->toBe(1);
    expect((int) data_get($decoded, 'source.items_count'))->toBe(1);
    expect((float) data_get($decoded, 'source.total_quantity_shipped'))->toBe(3.5);
});
