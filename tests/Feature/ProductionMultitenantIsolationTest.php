<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Production\Support\ProductionTenantAccess;
use Modules\Purchase\Entities\SalesShipment;
use Modules\Purchase\Entities\SalesShipmentItem;
use Modules\Purchase\Services\SalesDoService;
use Modules\Warehouse\Services\SalesShipmentStockService;

it('does not block sales DO when production module is disabled for the company', function (): void {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    session(['user' => (object) ['id' => 999]]);

    Schema::create('orders', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->timestamps();
    });

    DB::table('orders')->insert([
        'id' => 99,
        'company_id' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Schema::create('warehouses', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->string('name')->nullable();
        $table->timestamps();
    });

    DB::table('warehouses')->insert([
        'id' => 1,
        'company_id' => 10,
        'name' => 'WH-1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Schema::create('sales_shipments', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('order_id');
        $table->unsignedBigInteger('warehouse_id');
        $table->string('shipment_number');
        $table->date('shipment_date');
        $table->string('status')->default('draft');
        $table->boolean('outbound_stock_applied')->default(false);
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->timestamps();
    });

    Schema::create('sales_shipment_items', function ($table): void {
        $table->id();
        $table->unsignedBigInteger('sales_shipment_id');
        $table->unsignedBigInteger('order_item_id');
        $table->unsignedBigInteger('product_id');
        $table->decimal('quantity_ordered', 15, 4);
        $table->decimal('quantity_shipped', 15, 4);
        $table->timestamps();
    });

    Schema::create('production_orders', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('sales_order_id')->nullable();
        $table->string('status', 32)->default('draft');
        $table->timestamps();
    });

    Schema::create('module_settings', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->string('module_name');
        $table->string('type');
        $table->string('status')->default('active');
        $table->unsignedTinyInteger('is_allowed')->default(1);
        $table->timestamps();
    });

    Config::set('production.phase2.enforce_quality_lock_sales_do', true);

    $companyId = 10;

    expect(ProductionTenantAccess::productionEnabledForCompanyId($companyId))->toBeFalse();

    $mockStock = Mockery::mock(SalesShipmentStockService::class);
    $service = new SalesDoService($mockStock);

    $shipment = SalesShipment::create([
        'company_id' => $companyId,
        'order_id' => 99,
        'warehouse_id' => 1,
        'shipment_number' => 'SS-B2B-' . uniqid(),
        'shipment_date' => now()->toDateString(),
        'status' => 'confirmed',
    ]);

    SalesShipmentItem::create([
        'sales_shipment_id' => $shipment->id,
        'order_item_id' => 901,
        'product_id' => 201,
        'quantity_ordered' => 10,
        'quantity_shipped' => 3,
    ]);

    DB::table('production_orders')->insert([
        'company_id' => $companyId,
        'sales_order_id' => 99,
        'status' => 'released',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $mockStock->shouldReceive('ensureReservationsForShipment')->once();
    $mockStock->shouldReceive('applyOutboundForShipment')->once();

    expect($service->ship($shipment->fresh('items')))->toBeNull();
});
