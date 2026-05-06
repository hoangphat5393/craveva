<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Production\Entities\ProductionCompanyFgPolicy;
use Modules\Production\Entities\ProductionOrder;
use Modules\Production\Services\ProductionFgQuantityPolicyService;

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

it('allows controlled totals within configured tolerance without a variance reason', function (): void {
    $service = app(ProductionFgQuantityPolicyService::class);

    $order = new ProductionOrder([
        'company_id' => 1,
        'planned_quantity' => 20,
    ]);

    expect(fn () => $service->assertProjectedTotalsAllowedForOrder($order, 21.0, null))
        ->not->toThrow(InvalidArgumentException::class);
});

it('requires a variance reason for controlled totals beyond configured tolerance', function (): void {
    $service = app(ProductionFgQuantityPolicyService::class);

    $order = new ProductionOrder([
        'company_id' => 1,
        'planned_quantity' => 20,
    ]);

    $service->assertProjectedTotalsAllowedForOrder($order, 21.0001, null);
})->throws(InvalidArgumentException::class);

it('allows controlled totals beyond tolerance when a variance reason is provided', function (): void {
    $service = app(ProductionFgQuantityPolicyService::class);

    $order = new ProductionOrder([
        'company_id' => 1,
        'planned_quantity' => 20,
    ]);

    expect(fn () => $service->assertProjectedTotalsAllowedForOrder($order, 21.0001, 'Extra yield'))
        ->not->toThrow(InvalidArgumentException::class);
});

it('blocks strict totals above planned quantity', function (): void {
    ProductionCompanyFgPolicy::query()->create([
        'company_id' => 1,
        'policy_mode' => ProductionFgQuantityPolicyService::MODE_STRICT,
        'tolerance_percent' => 5,
        'tolerance_absolute' => 0,
        'controlled_require_reason_beyond_tolerance' => true,
        'controlled_block_beyond_tolerance' => false,
    ]);

    $service = app(ProductionFgQuantityPolicyService::class);

    $order = new ProductionOrder([
        'company_id' => 1,
        'planned_quantity' => 20,
    ]);

    $service->assertProjectedTotalsAllowedForOrder($order, 20.0001, 'Should not matter');
})->throws(InvalidArgumentException::class);

it('blocks flexible totals above planned quantity without a variance reason', function (): void {
    ProductionCompanyFgPolicy::query()->create([
        'company_id' => 1,
        'policy_mode' => ProductionFgQuantityPolicyService::MODE_FLEXIBLE,
        'tolerance_percent' => 5,
        'tolerance_absolute' => 0,
        'controlled_require_reason_beyond_tolerance' => true,
        'controlled_block_beyond_tolerance' => false,
    ]);

    $service = app(ProductionFgQuantityPolicyService::class);

    $order = new ProductionOrder([
        'company_id' => 1,
        'planned_quantity' => 20,
    ]);

    $service->assertProjectedTotalsAllowedForOrder($order, 20.0001, null);
})->throws(InvalidArgumentException::class);

it('allows flexible totals above planned quantity with a variance reason', function (): void {
    ProductionCompanyFgPolicy::query()->create([
        'company_id' => 1,
        'policy_mode' => ProductionFgQuantityPolicyService::MODE_FLEXIBLE,
        'tolerance_percent' => 5,
        'tolerance_absolute' => 0,
        'controlled_require_reason_beyond_tolerance' => true,
        'controlled_block_beyond_tolerance' => false,
    ]);

    $service = app(ProductionFgQuantityPolicyService::class);

    $order = new ProductionOrder([
        'company_id' => 1,
        'planned_quantity' => 20,
    ]);

    expect(fn () => $service->assertProjectedTotalsAllowedForOrder($order, 25.0, 'Customer requested overrun'))
        ->not->toThrow(InvalidArgumentException::class);
});

it('blocks controlled totals beyond tolerance when blocking is enabled even with a variance reason', function (): void {
    ProductionCompanyFgPolicy::query()->create([
        'company_id' => 1,
        'policy_mode' => ProductionFgQuantityPolicyService::MODE_CONTROLLED,
        'tolerance_percent' => 5,
        'tolerance_absolute' => 0,
        'controlled_require_reason_beyond_tolerance' => true,
        'controlled_block_beyond_tolerance' => true,
    ]);

    $service = app(ProductionFgQuantityPolicyService::class);

    $order = new ProductionOrder([
        'company_id' => 1,
        'planned_quantity' => 20,
    ]);

    $service->assertProjectedTotalsAllowedForOrder($order, 21.0001, 'Extra yield');
})->throws(InvalidArgumentException::class);
