<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Warehouse\Services\EnsureDefaultWarehouseService;
use Tests\TestCase;

class EnsureDefaultWarehouseServiceTest extends TestCase
{
    private EnsureDefaultWarehouseService $service;

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

        $this->createWebhooksSettingsTable();

        $this->service = new EnsureDefaultWarehouseService;
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('webhooks_settings');
        Schema::dropIfExists('warehouses');

        parent::tearDown();
    }

    private function createWebhooksSettingsTable(): void
    {
        Schema::create('webhooks_settings', function ($table) {
            $table->id();
            $table->unsignedInteger('company_id')->nullable();
            $table->string('name')->nullable();
            $table->string('webhook_for')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function test_creates_default_warehouse_when_company_has_none(): void
    {
        $result = $this->service->ensureForCompany(1, 'Acme Co');

        expect($result['action'])->toBe('created')
            ->and($result['warehouse_name'])->toBe('Default Warehouse')
            ->and($result['warehouse_id'])->not->toBeNull();

        $this->assertDatabaseHas('warehouses', [
            'company_id' => 1,
            'name' => 'Default Warehouse',
            'code' => 'DFWH',
            'is_default' => 1,
            'status' => 'active',
        ]);
    }

    public function test_dry_run_does_not_create_warehouse(): void
    {
        $result = $this->service->ensureForCompany(2, 'Dry Run Co', dryRun: true);

        expect($result['action'])->toBe('created')
            ->and($result['warehouse_id'])->toBeNull()
            ->and(DB::table('warehouses')->where('company_id', 2)->count())->toBe(0);
    }

    public function test_returns_already_ok_when_single_default_exists(): void
    {
        DB::table('warehouses')->insert([
            'company_id' => 3,
            'name' => 'Main WH',
            'code' => 'MAIN',
            'warehouse_type' => 'normal',
            'status' => 'active',
            'is_default' => 1,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->service->ensureForCompany(3, 'Stable Co');

        expect($result['action'])->toBe('already_ok')
            ->and($result['warehouse_name'])->toBe('Main WH');
    }

    public function test_normalizes_multiple_defaults_to_one(): void
    {
        DB::table('warehouses')->insert([
            [
                'id' => 10,
                'company_id' => 4,
                'name' => 'Alpha',
                'code' => 'A',
                'warehouse_type' => 'normal',
                'status' => 'active',
                'is_default' => 1,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 11,
                'company_id' => 4,
                'name' => 'Beta',
                'code' => 'B',
                'warehouse_type' => 'normal',
                'status' => 'active',
                'is_default' => 1,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $result = $this->service->ensureForCompany(4, 'Multi Default Co');

        expect($result['action'])->toBe('set_default')
            ->and($result['warehouse_id'])->toBe(10);

        $defaults = DB::table('warehouses')
            ->where('company_id', 4)
            ->where('is_default', 1)
            ->pluck('id')
            ->all();

        expect($defaults)->toBe([10]);
    }

    public function test_resolve_default_warehouse_id_returns_active_default(): void
    {
        DB::table('warehouses')->insert([
            'company_id' => 5,
            'name' => 'Only WH',
            'code' => 'ONLY',
            'warehouse_type' => 'normal',
            'status' => 'active',
            'is_default' => 1,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id = $this->service->resolveDefaultWarehouseId(5);

        expect($id)->not->toBeNull();
    }
}
