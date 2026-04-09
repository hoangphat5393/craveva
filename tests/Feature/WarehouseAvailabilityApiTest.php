<?php

use App\Models\UserAuth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::create('companies', function ($table) {
        $table->id();
        $table->string('company_name')->default('Test Co');
        $table->string('app_name')->nullable();
        $table->string('company_email')->default('co@example.test');
        $table->string('company_phone')->default('0');
        $table->text('address')->nullable();
        $table->string('timezone')->default('UTC');
        $table->string('date_format', 20)->default('d-m-Y');
        $table->string('date_picker_format')->default('dd-mm-yyyy');
        $table->string('moment_format')->default('DD-MM-YYYY');
        $table->string('time_format', 20)->default('h:i a');
        $table->string('locale')->default('en');
        $table->timestamps();
    });

    Schema::create('user_auths', function ($table) {
        $table->id();
        $table->string('email')->unique();
        $table->string('password');
        $table->timestamp('email_verified_at')->nullable();
        $table->timestamps();
    });

    Schema::create('users', function ($table) {
        $table->id();
        $table->foreignId('user_auth_id')->nullable()->constrained('user_auths')->cascadeOnDelete();
        $table->unsignedInteger('company_id')->nullable();
        $table->string('name')->default('t');
        $table->string('email')->nullable();
        $table->string('status', 20)->default('active');
        $table->string('login', 20)->default('enable');
        $table->boolean('dark_theme')->default(false);
        $table->boolean('rtl')->default(false);
        $table->timestamps();
    });

    Schema::create('warehouses', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->string('name')->nullable();
        $table->string('code')->nullable();
        $table->string('warehouse_type')->default('normal');
        $table->string('status', 20)->default('active');
        $table->timestamps();
    });

    Schema::create('warehouse_product_batches', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('warehouse_id');
        $table->unsignedBigInteger('product_id');
        $table->decimal('quantity', 20, 4)->default(0);
        $table->decimal('reserved_quantity', 20, 4)->default(0);
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('warehouse_product_batches');
    Schema::dropIfExists('warehouses');
    Schema::dropIfExists('users');
    Schema::dropIfExists('user_auths');
    Schema::dropIfExists('companies');
});

it('returns availability for the authenticated user company without passing company_id', function () {
    $companyId = (int) DB::table('companies')->insertGetId([
        'company_name' => 'Co One',
        'company_email' => 'c1@example.test',
        'company_phone' => '1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $authId = DB::table('user_auths')->insertGetId([
        'email' => 'wh-api-1@example.test',
        'password' => bcrypt('secret'),
        'email_verified_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('users')->insert([
        'user_auth_id' => $authId,
        'company_id' => $companyId,
        'name' => 'Employee',
        'email' => 'wh-api-1@example.test',
        'status' => 'active',
        'login' => 'enable',
        'dark_theme' => 0,
        'rtl' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('warehouses')->insert([
        'company_id' => $companyId,
        'name' => 'Main',
        'code' => 'M1',
        'warehouse_type' => 'normal',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $userAuth = UserAuth::query()->findOrFail($authId);
    $this->actingAs($userAuth, 'web');

    $response = $this->getJson('/api/v1/warehouse/availability?product_id=1');

    $response->assertOk();
    $response->assertJsonPath('status', 'success');
    $response->assertJsonPath('data.company_id', $companyId);
    $response->assertJsonPath('data.product_id', 1);
});

it('rejects company_id that does not belong to the authenticated account', function () {
    $companyId = (int) DB::table('companies')->insertGetId([
        'company_name' => 'Co Two',
        'company_email' => 'c2@example.test',
        'company_phone' => '1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('companies')->insert([
        'company_name' => 'Other',
        'company_email' => 'other@example.test',
        'company_phone' => '1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $authId = DB::table('user_auths')->insertGetId([
        'email' => 'wh-api-2@example.test',
        'password' => bcrypt('secret'),
        'email_verified_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('users')->insert([
        'user_auth_id' => $authId,
        'company_id' => $companyId,
        'name' => 'Employee',
        'email' => 'wh-api-2@example.test',
        'status' => 'active',
        'login' => 'enable',
        'dark_theme' => 0,
        'rtl' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $userAuth = UserAuth::query()->findOrFail($authId);
    $this->actingAs($userAuth, 'web');

    $this->getJson('/api/v1/warehouse/availability?product_id=1&company_id=999')
        ->assertForbidden();
});

it('requires company_id when the account spans multiple companies', function () {
    $c1 = (int) DB::table('companies')->insertGetId([
        'company_name' => 'Co A',
        'company_email' => 'ca@example.test',
        'company_phone' => '1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $c2 = (int) DB::table('companies')->insertGetId([
        'company_name' => 'Co B',
        'company_email' => 'cb@example.test',
        'company_phone' => '1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $authId = DB::table('user_auths')->insertGetId([
        'email' => 'wh-api-3@example.test',
        'password' => bcrypt('secret'),
        'email_verified_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach ([$c1, $c2] as $companyId) {
        DB::table('users')->insert([
            'user_auth_id' => $authId,
            'company_id' => $companyId,
            'name' => 'Employee',
            'email' => 'wh-api-3@example.test',
            'status' => 'active',
            'login' => 'enable',
            'dark_theme' => 0,
            'rtl' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $userAuth = UserAuth::query()->findOrFail($authId);
    $this->actingAs($userAuth, 'web');

    $response = $this->getJson('/api/v1/warehouse/availability?product_id=1');

    $response->assertUnprocessable();
    expect($response->json('error.details.company_id'))->toBeArray()
        ->and($response->json('error.details.company_id.0'))->toContain('required');
});

it('allows choosing among authorized companies when company_id is provided', function () {
    $c1 = (int) DB::table('companies')->insertGetId([
        'company_name' => 'Co A2',
        'company_email' => 'ca2@example.test',
        'company_phone' => '1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $c2 = (int) DB::table('companies')->insertGetId([
        'company_name' => 'Co B2',
        'company_email' => 'cb2@example.test',
        'company_phone' => '1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $authId = DB::table('user_auths')->insertGetId([
        'email' => 'wh-api-4@example.test',
        'password' => bcrypt('secret'),
        'email_verified_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach ([$c1, $c2] as $companyId) {
        DB::table('users')->insert([
            'user_auth_id' => $authId,
            'company_id' => $companyId,
            'name' => 'Employee',
            'email' => 'wh-api-4@example.test',
            'status' => 'active',
            'login' => 'enable',
            'dark_theme' => 0,
            'rtl' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    DB::table('warehouses')->insert([
        'company_id' => $c2,
        'name' => 'Branch',
        'code' => 'B1',
        'warehouse_type' => 'normal',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $userAuth = UserAuth::query()->findOrFail($authId);
    $this->actingAs($userAuth, 'web');

    $response = $this->getJson('/api/v1/warehouse/availability?product_id=5&company_id=' . $c2);

    $response->assertOk();
    $response->assertJsonPath('data.company_id', $c2);
    $response->assertJsonPath('data.product_id', 5);
});

it('rejects unauthenticated warehouse availability requests', function () {
    $this->getJson('/api/v1/warehouse/availability?product_id=1')
        ->assertUnauthorized();
});
