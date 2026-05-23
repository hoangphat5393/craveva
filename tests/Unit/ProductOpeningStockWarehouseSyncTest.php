<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Entities\PurchaseInventory;
use Modules\Purchase\Entities\PurchaseProduct;
use Modules\Purchase\Entities\PurchaseStockAdjustment;
use Modules\Purchase\Services\ProductOpeningStockWarehouseSync;
use Modules\Warehouse\Services\StockMovementService;
use Tests\TestCase;

class ProductOpeningStockWarehouseSyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('warehouses', function ($table) {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('warehouse_type')->default('normal');
            $table->string('status')->default('active');
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('products', function ($table) {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->string('name')->nullable();
            $table->string('track_inventory')->default('0');
            $table->unsignedInteger('unit_id')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_inventory_adjustment', function ($table) {
            $table->id();
            $table->unsignedInteger('company_id')->nullable();
            $table->date('date')->nullable();
            $table->string('type')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_stock_adjustments', function ($table) {
            $table->id();
            $table->unsignedInteger('company_id')->nullable();
            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->date('date')->nullable();
            $table->string('type')->nullable();
            $table->decimal('net_quantity', 20, 4)->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('warehouse_product_stock', function ($table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity', 20, 4)->default(0);
            $table->timestamps();
        });

        Schema::create('webhooks_settings', function ($table) {
            $table->id();
            $table->unsignedInteger('company_id')->nullable();
            $table->string('name')->nullable();
            $table->string('webhook_for')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('webhooks_settings');
        Schema::dropIfExists('warehouse_product_stock');
        Schema::dropIfExists('purchase_stock_adjustments');
        Schema::dropIfExists('purchase_inventory_adjustment');
        Schema::dropIfExists('products');
        Schema::dropIfExists('warehouses');

        parent::tearDown();
    }

    public function test_can_sync_when_warehouse_tables_exist(): void
    {
        $sync = app(ProductOpeningStockWarehouseSync::class);

        expect($sync->canSync())->toBeTrue();
    }

    public function test_sync_throws_when_no_default_warehouse(): void
    {
        $product = $this->makeProduct(companyId: 1, productId: 100);
        $inventory = $this->makeInventory(companyId: 1);
        $stockLine = $this->makeStockLine(companyId: 1, productId: 100, inventoryId: $inventory->id);

        $sync = app(ProductOpeningStockWarehouseSync::class);

        $this->expectException(\RuntimeException::class);

        $sync->syncFromProductSave($product, $stockLine, $inventory, 50.0);
    }

    public function test_sync_sets_warehouse_id_and_records_inbound_movement(): void
    {
        $warehouseId = DB::table('warehouses')->insertGetId([
            'company_id' => 1,
            'name' => 'Default WH',
            'code' => 'DFWH',
            'warehouse_type' => 'normal',
            'status' => 'active',
            'is_default' => 1,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product = $this->makeProduct(companyId: 1, productId: 200);
        $inventory = $this->makeInventory(companyId: 1);
        $stockLine = $this->makeStockLine(companyId: 1, productId: 200, inventoryId: $inventory->id);

        $this->mock(StockMovementService::class, function ($mock) use ($warehouseId): void {
            $mock->shouldReceive('recordInbound')
                ->once()
                ->withArgs(function (array $payload) use ($warehouseId): bool {
                    return (int) $payload['warehouse_id'] === $warehouseId
                        && (int) $payload['product_id'] === 200
                        && (float) $payload['quantity'] === 75.0;
                });
        });

        $sync = app(ProductOpeningStockWarehouseSync::class);
        $sync->syncFromProductSave($product, $stockLine->fresh(), $inventory->fresh(), 75.0);

        expect((int) $stockLine->fresh()->warehouse_id)->toBe($warehouseId)
            ->and((int) $inventory->fresh()->warehouse_id)->toBe($warehouseId);
    }

    public function test_backfill_dry_run_reports_would_sync_without_writes(): void
    {
        $warehouseId = DB::table('warehouses')->insertGetId([
            'company_id' => 2,
            'name' => 'WH-2',
            'code' => 'WH2',
            'warehouse_type' => 'normal',
            'status' => 'active',
            'is_default' => 1,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $inventoryId = DB::table('purchase_inventory_adjustment')->insertGetId([
            'company_id' => 2,
            'date' => now()->toDateString(),
            'type' => 'quantity',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_stock_adjustments')->insert([
            'company_id' => 2,
            'inventory_id' => $inventoryId,
            'product_id' => 300,
            'warehouse_id' => null,
            'date' => now()->toDateString(),
            'type' => 'quantity',
            'net_quantity' => 40,
            'status' => 'converted',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'id' => 300,
            'company_id' => 2,
            'name' => 'Legacy Product',
            'track_inventory' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sync = app(ProductOpeningStockWarehouseSync::class);
        $results = $sync->backfillLegacyLinesForCompany(2, dryRun: true);

        expect($results)->toHaveCount(1)
            ->and($results[0]['action'])->toBe('would_sync')
            ->and($results[0]['note'])->toContain((string) $warehouseId);

        expect(DB::table('purchase_stock_adjustments')->whereNull('warehouse_id')->count())->toBe(1);
    }

    private function makeProduct(int $companyId, int $productId): PurchaseProduct
    {
        DB::table('products')->insert([
            'id' => $productId,
            'company_id' => $companyId,
            'name' => 'Test Product',
            'track_inventory' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return PurchaseProduct::withoutGlobalScopes()->findOrFail($productId);
    }

    private function makeInventory(int $companyId): PurchaseInventory
    {
        $inventory = new PurchaseInventory;
        $inventory->company_id = $companyId;
        $inventory->date = now()->toDateString();
        $inventory->type = 'quantity';
        $inventory->save();

        return $inventory;
    }

    private function makeStockLine(int $companyId, int $productId, int $inventoryId): PurchaseStockAdjustment
    {
        $stockLine = new PurchaseStockAdjustment;
        $stockLine->company_id = $companyId;
        $stockLine->product_id = $productId;
        $stockLine->inventory_id = $inventoryId;
        $stockLine->date = now()->toDateString();
        $stockLine->type = 'quantity';
        $stockLine->net_quantity = 10;
        $stockLine->status = 'converted';
        $stockLine->save();

        return $stockLine;
    }
}
