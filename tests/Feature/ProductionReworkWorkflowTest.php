<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Production\Entities\ProductionBatch;
use Modules\Production\Entities\ProductionReworkOrder;

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

    Schema::create('production_orders', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('status', 32)->default('draft');
        $table->unsignedBigInteger('output_product_id')->nullable();
        $table->unsignedBigInteger('production_bom_id')->nullable();
        $table->unsignedBigInteger('rm_warehouse_id')->nullable();
        $table->unsignedBigInteger('fg_warehouse_id')->nullable();
        $table->decimal('planned_quantity', 20, 4)->default(0);
        $table->timestamps();
    });

    Schema::create('production_batches', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->unsignedBigInteger('production_order_id');
        $table->string('batch_code');
        $table->timestamp('posted_consumptions_at')->nullable();
        $table->timestamp('posted_receipt_at')->nullable();
        $table->timestamp('completed_at')->nullable();
        $table->timestamps();
    });

    $reworkMigration = require __DIR__.'/../../Modules/Production/Database/Migrations/2026_05_06_190849_create_production_rework_orders_table.php';
    $reworkMigration->up();
});

afterEach(function (): void {
    Schema::dropIfExists('production_rework_orders');
    Schema::dropIfExists('production_batches');
    Schema::dropIfExists('production_orders');
    Schema::dropIfExists('companies');
});

it('stores and transitions rework order statuses', function (): void {
    DB::table('companies')->insert([
        'id' => 1,
        'company_name' => 'Acme',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $orderId = DB::table('production_orders')->insertGetId([
        'company_id' => 1,
        'status' => 'in_progress',
        'planned_quantity' => 100,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $batch = ProductionBatch::query()->create([
        'company_id' => 1,
        'production_order_id' => $orderId,
        'batch_code' => 'PB-RW-01',
    ]);

    $rework = ProductionReworkOrder::query()->create([
        'company_id' => 1,
        'source_production_batch_id' => $batch->id,
        'requested_quantity' => 12.5,
        'reason' => 'QC failed at final visual check',
        'status' => ProductionReworkOrder::STATUS_REQUESTED,
        'requested_by' => 99,
    ]);

    expect($rework->status)->toBe(ProductionReworkOrder::STATUS_REQUESTED);

    $rework->status = ProductionReworkOrder::STATUS_APPROVED;
    $rework->approved_quantity = 10.0;
    $rework->approved_by = 101;
    $rework->approved_at = now();
    $rework->save();

    expect($rework->fresh()->status)->toBe(ProductionReworkOrder::STATUS_APPROVED)
        ->and($rework->fresh()->approved_quantity)->toBe(10.0)
        ->and($rework->fresh()->approved_at)->not->toBeNull();

    $rework->refresh();
    $rework->status = ProductionReworkOrder::STATUS_COMPLETED;
    $rework->completed_at = now();
    $rework->save();

    expect($rework->fresh()->status)->toBe(ProductionReworkOrder::STATUS_COMPLETED)
        ->and($rework->fresh()->completed_at)->not->toBeNull();
});
