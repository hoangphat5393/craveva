<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Production\Entities\ProductionBatchOutput;
use Modules\Purchase\Entities\PurchaseStockAdjustment;
use Modules\Purchase\Services\ProductionFgInventoryLedgerSync;
use Tests\TestCase;

class ProductionFgInventoryLedgerSyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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
            $table->timestamps();
        });

        Schema::create('warehouses', function ($table): void {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->string('name')->nullable();
            $table->boolean('is_default')->default(true);
            $table->timestamps();
        });

        Schema::create('purchase_inventory_adjustment', function ($table): void {
            $table->id();
            $table->unsignedInteger('company_id')->nullable();
            $table->date('date')->nullable();
            $table->string('type')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_stock_adjustments', function ($table): void {
            $table->id();
            $table->unsignedInteger('company_id')->nullable();
            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->string('type')->nullable();
            $table->date('date')->nullable();
            $table->string('batch_number')->nullable();
            $table->decimal('net_quantity', 20, 4)->nullable();
            $table->string('status')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('warehouse_product_stock', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity', 20, 4)->default(0);
            $table->timestamps();
        });

        Schema::create('production_batch_outputs', function ($table): void {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->unsignedBigInteger('production_batch_id')->nullable();
            $table->unsignedBigInteger('output_product_id');
            $table->decimal('quantity', 20, 4);
            $table->string('batch_number')->nullable();
            $table->unsignedBigInteger('warehouse_id');
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('webhooks_settings', function ($table): void {
            $table->id();
            $table->unsignedInteger('company_id')->nullable();
            $table->string('name')->nullable();
            $table->string('webhook_for')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        DB::table('companies')->insert(['id' => 1, 'company_name' => 'Co', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('products')->insert(['id' => 10, 'company_id' => 1, 'name' => 'FG Cake', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('warehouses')->insert(['id' => 39, 'company_id' => 1, 'name' => 'Default', 'is_default' => true, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('warehouse_product_stock')->insert([
            'warehouse_id' => 39,
            'product_id' => 10,
            'quantity' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('webhooks_settings');
        Schema::dropIfExists('production_batch_outputs');
        Schema::dropIfExists('warehouse_product_stock');
        Schema::dropIfExists('purchase_stock_adjustments');
        Schema::dropIfExists('purchase_inventory_adjustment');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('products');
        Schema::dropIfExists('companies');

        parent::tearDown();
    }

    public function test_creates_ledger_line_with_warehouse_on_hand_after_fg_post(): void
    {
        $output = new ProductionBatchOutput;
        $output->id = 99;
        $output->company_id = 1;
        $output->output_product_id = 10;
        $output->warehouse_id = 39;
        $output->quantity = 3;
        $output->batch_number = 'GAGA';
        $output->posted_at = now();
        $output->exists = true;

        app(ProductionFgInventoryLedgerSync::class)->ensureLedgerLineAfterFgReceipt($output);

        $line = PurchaseStockAdjustment::withoutGlobalScopes()
            ->where('company_id', 1)
            ->where('product_id', 10)
            ->where('warehouse_id', 39)
            ->first();

        expect($line)->not->toBeNull()
            ->and((float) $line->net_quantity)->toBe(3.0)
            ->and($line->batch_number)->toBe('GAGA')
            ->and($line->reference_number)->toBe('PROD-OUT-99');
    }

    public function test_skips_when_output_not_posted(): void
    {
        $output = new ProductionBatchOutput;
        $output->company_id = 1;
        $output->output_product_id = 10;
        $output->warehouse_id = 39;
        $output->posted_at = null;
        $output->exists = true;

        app(ProductionFgInventoryLedgerSync::class)->ensureLedgerLineAfterFgReceipt($output);

        expect(
            PurchaseStockAdjustment::withoutGlobalScopes()->where('product_id', 10)->count()
        )->toBe(0);
    }
}
