<?php

use App\Services\Company\CompanyTransactionPurgePlan;
use App\Services\Company\CompanyTransactionPurgeService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    Config::set('company-purge.allow_execute', false);
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::create('companies', function ($table) {
        $table->increments('id');
        $table->string('company_name');
        $table->timestamps();
    });

    Schema::create('orders', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->timestamps();
    });

    Schema::create('order_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('order_id');
        $table->timestamps();
    });

    Schema::create('products', function ($table) {
        $table->increments('id');
        $table->unsignedInteger('company_id');
        $table->string('name')->nullable();
        $table->timestamps();
    });

    Schema::create('stock_movements', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('movement_type')->default('inbound');
        $table->timestamps();
    });

    Schema::create('production_boms', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('code');
        $table->timestamps();
    });

    Schema::create('production_bom_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('production_bom_id');
        $table->timestamps();
    });

    Schema::create('unrelated_records', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('name');
        $table->timestamps();
    });

    DB::table('companies')->insert([
        'id' => 1,
        'company_name' => 'Acme Demo',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('orders')->insert([
        ['id' => 10, 'company_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ['id' => 11, 'company_id' => 2, 'created_at' => now(), 'updated_at' => now()],
    ]);

    DB::table('order_items')->insert([
        ['id' => 100, 'order_id' => 10, 'created_at' => now(), 'updated_at' => now()],
        ['id' => 101, 'order_id' => 11, 'created_at' => now(), 'updated_at' => now()],
    ]);

    DB::table('products')->insert([
        'id' => 1,
        'company_id' => 1,
        'name' => 'Keep me',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('stock_movements')->insert([
        'company_id' => 1,
        'movement_type' => 'inbound',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('production_boms')->insert([
        ['id' => 20, 'company_id' => 1, 'code' => 'BOM-C1', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 21, 'company_id' => 2, 'code' => 'BOM-C2', 'created_at' => now(), 'updated_at' => now()],
    ]);
    DB::table('production_bom_items')->insert([
        ['id' => 200, 'production_bom_id' => 20, 'created_at' => now(), 'updated_at' => now()],
        ['id' => 201, 'production_bom_id' => 21, 'created_at' => now(), 'updated_at' => now()],
    ]);
    DB::table('unrelated_records')->insert([
        'company_id' => 1,
        'name' => 'Never delete me',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

afterEach(function () {
    Schema::dropIfExists('unrelated_records');
    Schema::dropIfExists('production_bom_items');
    Schema::dropIfExists('production_boms');
    Schema::dropIfExists('stock_movements');
    Schema::dropIfExists('order_items');
    Schema::dropIfExists('orders');
    Schema::dropIfExists('products');
    Schema::dropIfExists('companies');
});

it('dry-run counts rows but does not delete', function () {
    $this->artisan('company:purge-transactions', ['--company-id' => 1])
        ->expectsOutputToContain('DRY-RUN')
        ->expectsOutputToContain('Dry-run only')
        ->assertExitCode(0);

    expect(DB::table('orders')->where('company_id', 1)->count())->toBe(1)
        ->and(DB::table('order_items')->count())->toBe(2)
        ->and(DB::table('products')->where('company_id', 1)->count())->toBe(1);
});

it('blocks execute without COMPANY_PURGE_ALLOW_EXECUTE', function () {
    $this->artisan('company:purge-transactions', [
        '--company-id' => 1,
        '--execute' => true,
        '--confirm-token' => 'PURGE-1-acme-demo',
        '--no-interaction' => true,
    ])
        ->expectsOutputToContain('COMPANY_PURGE_ALLOW_EXECUTE')
        ->assertExitCode(1);

    expect(DB::table('orders')->where('company_id', 1)->count())->toBe(1);
});

it('deletes only targeted company when execute is allowed', function () {
    Config::set('company-purge.allow_execute', true);

    $this->artisan('company:purge-transactions', [
        '--company-id' => 1,
        '--execute' => true,
        '--confirm-token' => 'PURGE-1-acme-demo',
        '--no-interaction' => true,
    ])
        ->expectsOutputToContain('EXECUTE')
        ->assertExitCode(0);

    expect(DB::table('orders')->where('company_id', 1)->count())->toBe(0)
        ->and(DB::table('orders')->where('company_id', 2)->count())->toBe(1)
        ->and(DB::table('order_items')->where('order_id', 10)->count())->toBe(0)
        ->and(DB::table('products')->where('company_id', 1)->count())->toBe(1)
        ->and(DB::table('production_boms')->where('company_id', 1)->count())->toBe(1)
        ->and(DB::table('unrelated_records')->count())->toBe(1);
});

it('deletes bom master only with the explicit include-boms option', function () {
    Config::set('company-purge.allow_execute', true);

    $this->artisan('company:purge-transactions', [
        '--company-id' => 1,
        '--include-boms' => true,
        '--execute' => true,
        '--confirm-token' => 'PURGE-1-acme-demo-WITH-BOMS',
        '--no-interaction' => true,
    ])->assertExitCode(0);

    expect(DB::table('production_boms')->where('company_id', 1)->count())->toBe(0)
        ->and(DB::table('production_bom_items')->where('production_bom_id', 20)->count())->toBe(0)
        ->and(DB::table('production_boms')->where('company_id', 2)->count())->toBe(1)
        ->and(DB::table('production_bom_items')->where('production_bom_id', 21)->count())->toBe(1)
        ->and(DB::table('products')->where('company_id', 1)->count())->toBe(1)
        ->and(DB::table('unrelated_records')->count())->toBe(1);
});

it('rolls back every deletion when a later purge step fails', function () {
    DB::statement("CREATE TRIGGER block_order_delete BEFORE DELETE ON orders BEGIN SELECT RAISE(ABORT, 'blocked order delete'); END");

    expect(fn () => app(CompanyTransactionPurgeService::class)->execute(1, true))
        ->toThrow(QueryException::class);

    expect(DB::table('stock_movements')->where('company_id', 1)->count())->toBe(1)
        ->and(DB::table('orders')->where('company_id', 1)->count())->toBe(1)
        ->and(DB::table('production_boms')->where('company_id', 1)->count())->toBe(1);
});

it('uses an explicit allowlist that excludes master and unrelated tables', function () {
    $tables = collect(CompanyTransactionPurgePlan::steps(includeBoms: true))->pluck('table');

    expect($tables)->not->toContain('companies')
        ->not->toContain('users')
        ->not->toContain('client_details')
        ->not->toContain('products')
        ->not->toContain('purchase_vendors')
        ->not->toContain('warehouses')
        ->not->toContain('projects')
        ->not->toContain('tasks')
        ->not->toContain('bank_transactions')
        ->not->toContain('project_time_logs')
        ->not->toContain('purchase_product_histories')
        ->not->toContain('purchase_vendor_histories')
        ->not->toContain('unrelated_records');
});
