<?php

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
});

afterEach(function () {
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
        ->and(DB::table('products')->where('company_id', 1)->count())->toBe(1);
});
