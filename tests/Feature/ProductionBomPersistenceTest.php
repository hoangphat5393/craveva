<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Entities\ProductionBomItem;
use Modules\Production\Entities\ProductionOrder;

beforeEach(function (): void {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::create('companies', function ($table): void {
        $table->increments('id');
        $table->string('company_name')->default('Test Co');
        $table->timestamps();
    });

    Schema::create('products', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('name')->nullable();
        $table->string('type')->default('goods');
        $table->timestamps();
    });

    Schema::create('warehouses', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('name')->nullable();
        $table->string('warehouse_type')->default('normal');
        $table->boolean('is_default')->default(false);
        $table->string('status')->default('active');
        $table->timestamps();
    });

    $migration = require __DIR__.'/../../Modules/Production/Database/Migrations/2026_05_05_100000_create_production_mvp_tables.php';
    $migration->up();

    $productionFgQuantityPolicyMigration = require __DIR__.'/../../Modules/Production/Database/Migrations/2026_05_06_120000_add_production_fg_policy_and_variance_columns.php';
    $productionFgQuantityPolicyMigration->up();

    DB::table('companies')->insert([
        'company_name' => 'Acme',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('products')->insert([
        ['id' => 1, 'company_id' => 1, 'name' => 'RM-A', 'type' => 'raw_material', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'company_id' => 1, 'name' => 'FG-B', 'type' => 'goods', 'created_at' => now(), 'updated_at' => now()],
    ]);

    DB::table('warehouses')->insert([
        'company_id' => 1,
        'name' => 'Main',
        'warehouse_type' => 'normal',
        'is_default' => true,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

afterEach(function (): void {
    Schema::dropIfExists('production_company_fg_policies');
    Schema::dropIfExists('production_batch_outputs');
    Schema::dropIfExists('production_batch_consumptions');
    Schema::dropIfExists('production_batches');
    Schema::dropIfExists('production_orders');
    Schema::dropIfExists('production_bom_items');
    Schema::dropIfExists('production_boms');
    Schema::dropIfExists('warehouses');
    Schema::dropIfExists('products');
    Schema::dropIfExists('companies');
});

it('persists BOM items for a tenant bom', function (): void {
    $bom = ProductionBom::query()->create([
        'company_id' => 1,
        'output_product_id' => 2,
        'version' => 'v1',
        'code' => 'BOM-1',
        'effective_from' => null,
        'effective_to' => null,
        'is_default' => true,
        'notes' => null,
    ]);

    ProductionBomItem::query()->create([
        'company_id' => 1,
        'production_bom_id' => $bom->id,
        'component_product_id' => 1,
        'quantity' => 0.25,
        'unit_id' => null,
        'sort_order' => 0,
    ]);

    ProductionBomItem::query()->create([
        'company_id' => 1,
        'production_bom_id' => $bom->id,
        'component_product_id' => 1,
        'quantity' => 1.5,
        'unit_id' => null,
        'sort_order' => 1,
    ]);

    expect($bom->items()->count())->toBe(2);
});

it('detects BOM usage through production orders', function (): void {
    $bom = ProductionBom::query()->create([
        'company_id' => 1,
        'output_product_id' => 2,
        'version' => 'v-used',
        'code' => null,
        'effective_from' => null,
        'effective_to' => null,
        'is_default' => false,
        'notes' => null,
    ]);

    ProductionOrder::query()->create([
        'company_id' => 1,
        'status' => ProductionOrder::STATUS_DRAFT,
        'output_product_id' => 2,
        'production_bom_id' => $bom->id,
        'rm_warehouse_id' => 1,
        'fg_warehouse_id' => 1,
        'planned_quantity' => 10,
    ]);

    expect($bom->productionOrders()->exists())->toBeTrue();
});
