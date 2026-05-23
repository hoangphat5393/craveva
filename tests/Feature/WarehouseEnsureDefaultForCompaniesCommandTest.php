<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::create('companies', function ($table) {
        $table->increments('id');
        $table->string('company_name');
        $table->string('status')->default('active');
        $table->timestamps();
    });

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

    DB::table('companies')->insert([
        [
            'id' => 1,
            'company_name' => 'No Warehouse Co',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 2,
            'company_name' => 'Has Default Co',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    DB::table('warehouses')->insert([
        'company_id' => 2,
        'name' => 'Existing Default',
        'code' => 'EX',
        'warehouse_type' => 'normal',
        'status' => 'active',
        'is_default' => 1,
        'sort_order' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

afterEach(function () {
    Schema::dropIfExists('warehouses');
    Schema::dropIfExists('companies');
});

it('dry run reports would create without writing warehouses', function () {
    $this->artisan('warehouse:ensure-default-for-companies', ['--dry-run' => true])
        ->expectsOutputToContain('Dry run')
        ->expectsOutputToContain('No Warehouse Co')
        ->assertExitCode(0);

    expect(DB::table('warehouses')->where('company_id', 1)->count())->toBe(0)
        ->and(DB::table('warehouses')->where('company_id', 2)->count())->toBe(1);
});

it('dry run for single company filters results', function () {
    $this->artisan('warehouse:ensure-default-for-companies', [
        '--dry-run' => true,
        '--company' => 2,
    ])
        ->expectsOutputToContain('Has Default Co')
        ->expectsOutputToContain('already_ok')
        ->assertExitCode(0);
});
