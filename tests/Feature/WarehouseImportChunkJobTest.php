<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Warehouse\Jobs\ImportWarehouseChunkJob;

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::create('warehouses', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->string('name');
        $table->string('code')->nullable();
        $table->string('address')->nullable();
        $table->text('description')->nullable();
        $table->boolean('is_default')->default(false);
        $table->string('status')->default('active');
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('warehouses');
});

it('upserts warehouse by company and code', function () {
    DB::table('warehouses')->insert([
        'company_id' => 99,
        'name' => 'Old Name',
        'code' => 'WH-A',
        'status' => 'active',
        'is_default' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $rows = [
        ['WH-A', 'New Name', 'inactive', 'Addr A', 'Desc A', '1'],
    ];
    $columns = ['warehouse_code', 'warehouse_name', 'status', 'address', 'description', 'is_default'];
    $company = (object) ['id' => 99];

    (new ImportWarehouseChunkJob($rows, $columns, $company, 0))->handle();

    expect(DB::table('warehouses')->where('company_id', 99)->count())->toBe(1);
    $row = DB::table('warehouses')->where('company_id', 99)->where('code', 'WH-A')->first();
    expect($row)->not->toBeNull();
    expect($row->name)->toBe('New Name');
    expect($row->status)->toBe('inactive');
    expect((int) $row->is_default)->toBe(1);
});

it('updates duplicated code using latest row data', function () {
    $rows = [
        ['WH-DUP', 'First Name', 'active', 'Addr 1', 'Desc 1', '0'],
        ['WH-DUP', 'Second Name', 'inactive', 'Addr 2', 'Desc 2', '1'],
    ];
    $columns = ['warehouse_code', 'warehouse_name', 'status', 'address', 'description', 'is_default'];
    $company = (object) ['id' => 77];

    (new ImportWarehouseChunkJob($rows, $columns, $company, 0))->handle();

    expect(DB::table('warehouses')->where('company_id', 77)->count())->toBe(1);
    $row = DB::table('warehouses')->where('company_id', 77)->where('code', 'WH-DUP')->first();
    expect($row->name)->toBe('Second Name');
    expect($row->status)->toBe('inactive');
    expect($row->address)->toBe('Addr 2');
    expect((int) $row->is_default)->toBe(1);
});

it('skips rows missing warehouse_name or warehouse_code', function () {
    $rows = [
        ['WH-X', '', 'active', '', '', '0'],
        ['', 'No Code Warehouse', 'active', '', '', '0'],
        ['WH-OK', 'Valid Warehouse', 'active', '', '', '0'],
    ];
    $columns = ['warehouse_code', 'warehouse_name', 'status', 'address', 'description', 'is_default'];
    $company = (object) ['id' => 12];

    (new ImportWarehouseChunkJob($rows, $columns, $company, 0))->handle();
    expect(DB::table('warehouses')->where('company_id', 12)->count())->toBe(1);
    expect(DB::table('warehouses')->where('company_id', 12)->where('code', 'WH-OK')->exists())->toBeTrue();
});
