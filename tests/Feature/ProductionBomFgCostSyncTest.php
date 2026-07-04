<?php

declare(strict_types=1);

use App\Models\Product;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Entities\ProductionBomItem;
use Modules\Production\Services\ProductionBomFgCostSyncService;

beforeEach(function (): void {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    Config::set('production.cost_sync.bom_drives_fg_purchase_price', true);
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::create('companies', function ($table): void {
        $table->increments('id');
        $table->string('company_name')->default('Test Co');
        $table->timestamps();
    });

    Schema::create('unit_types', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->string('unit_type')->nullable();
        $table->timestamps();
    });

    Schema::create('products', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('name')->nullable();
        $table->string('type')->default('goods');
        $table->string('purchase_price')->nullable();
        $table->boolean('cost_from_bom')->default(false);
        $table->unsignedBigInteger('unit_id')->nullable();
        $table->timestamps();
    });

    $migration = require __DIR__.'/../../Modules/Production/Database/Migrations/2026_05_05_100000_create_production_mvp_tables.php';
    $migration->up();

    $wasteMigration = require __DIR__.'/../../Modules/Production/Database/Migrations/2026_05_20_160000_add_waste_percent_to_production_bom_tables.php';
    $wasteMigration->up();

    DB::table('companies')->insert([
        'company_name' => 'Acme',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('unit_types')->insert([
        'company_id' => 1,
        'unit_type' => 'pcs',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('products')->insert([
        [
            'id' => 1,
            'company_id' => 1,
            'name' => 'RM-A',
            'type' => 'raw_material',
            'purchase_price' => '4',
            'cost_from_bom' => false,
            'unit_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 2,
            'company_id' => 1,
            'name' => 'FG-B',
            'type' => 'goods',
            'purchase_price' => '10',
            'cost_from_bom' => true,
            'unit_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
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
    Schema::dropIfExists('products');
    Schema::dropIfExists('unit_types');
    Schema::dropIfExists('companies');
});

it('syncs fg purchase price from bom total when custom is enabled', function (): void {
    $bom = ProductionBom::query()->create([
        'company_id' => 1,
        'output_product_id' => 2,
        'version' => 'v1',
        'is_default' => true,
    ]);

    ProductionBomItem::query()->create([
        'company_id' => 1,
        'production_bom_id' => $bom->id,
        'component_product_id' => 1,
        'quantity' => 1,
    ]);

    $bom->load(['items']);

    $synced = app(ProductionBomFgCostSyncService::class)->syncOutputProductFromBom($bom, 1);

    expect($synced)->toBeTrue();

    $fg = Product::withoutGlobalScopes()->find(2);

    expect($fg)->not->toBeNull()
        ->and((float) $fg->purchase_price)->toBe(4.0);
});

it('does not sync when tenant flag is disabled', function (): void {
    Config::set('production.cost_sync.bom_drives_fg_purchase_price', false);

    $bom = ProductionBom::query()->create([
        'company_id' => 1,
        'output_product_id' => 2,
        'version' => 'v1',
        'is_default' => true,
    ]);

    ProductionBomItem::query()->create([
        'company_id' => 1,
        'production_bom_id' => $bom->id,
        'component_product_id' => 1,
        'quantity' => 1,
    ]);

    $bom->load(['items']);

    $synced = app(ProductionBomFgCostSyncService::class)->syncOutputProductFromBom($bom, 1);

    expect($synced)->toBeFalse()
        ->and((float) Product::withoutGlobalScopes()->find(2)->purchase_price)->toBe(10.0);
});

it('does not sync when fg custom flag is off', function (): void {
    Product::withoutGlobalScopes()->where('id', 2)->update(['cost_from_bom' => false]);

    $bom = ProductionBom::query()->create([
        'company_id' => 1,
        'output_product_id' => 2,
        'version' => 'v1',
        'is_default' => true,
    ]);

    ProductionBomItem::query()->create([
        'company_id' => 1,
        'production_bom_id' => $bom->id,
        'component_product_id' => 1,
        'quantity' => 1,
    ]);

    $bom->load(['items']);

    $synced = app(ProductionBomFgCostSyncService::class)->syncOutputProductFromBom($bom, 1);

    expect($synced)->toBeFalse()
        ->and((float) Product::withoutGlobalScopes()->find(2)->purchase_price)->toBe(10.0);
});

it('does not sync when raw material cost is missing', function (): void {
    Product::withoutGlobalScopes()->where('id', 1)->update(['purchase_price' => null]);

    $bom = ProductionBom::query()->create([
        'company_id' => 1,
        'output_product_id' => 2,
        'version' => 'v1',
        'is_default' => true,
    ]);

    ProductionBomItem::query()->create([
        'company_id' => 1,
        'production_bom_id' => $bom->id,
        'component_product_id' => 1,
        'quantity' => 1,
    ]);

    $bom->load(['items']);

    $synced = app(ProductionBomFgCostSyncService::class)->syncOutputProductFromBom($bom, 1);

    expect($synced)->toBeFalse()
        ->and((float) Product::withoutGlobalScopes()->find(2)->purchase_price)->toBe(10.0);
});
