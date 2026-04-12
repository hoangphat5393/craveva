<?php

use App\Scopes\CompanyScope;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Entities\PurchaseVendorCredit;
use Modules\Purchase\Entities\PurchaseVendorItem;
use Modules\Warehouse\Services\StockMovementService;
use Modules\Warehouse\Services\VendorCreditWarehouseStockService;

beforeEach(function () {
    $stockMovement = app(StockMovementService::class);

    $vendorCreditStockStub = new class($stockMovement) extends VendorCreditWarehouseStockService
    {
        protected function isLedgerIntegrationEnabled(): bool
        {
            return true;
        }
    };

    $this->app->instance(VendorCreditWarehouseStockService::class, $vendorCreditStockStub);

    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Config::set('warehouse.allow_negative_stock', false);

    Schema::create('warehouses', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('name')->nullable();
        $table->string('warehouse_type')->default('normal');
        $table->boolean('is_default')->default(false);
        $table->string('status')->default('active');
        $table->timestamps();
    });

    Schema::create('products', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('name')->nullable();
        $table->string('type')->default('goods');
        $table->timestamps();
    });

    Schema::create('warehouse_product_batches', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->unsignedBigInteger('warehouse_id');
        $table->unsignedBigInteger('product_id');
        $table->string('batch_number')->nullable();
        $table->date('expiration_date')->nullable();
        $table->date('manufacturing_date')->nullable();
        $table->decimal('quantity', 20, 4)->default(0);
        $table->decimal('reserved_quantity', 20, 4)->default(0);
        $table->timestamps();
    });

    Schema::create('warehouse_product_stock', function ($table) {
        $table->id();
        $table->unsignedBigInteger('warehouse_id');
        $table->unsignedBigInteger('product_id');
        $table->decimal('quantity', 20, 4)->default(0);
        $table->timestamps();
    });

    Schema::create('stock_movements', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('product_id')->nullable();
        $table->unsignedBigInteger('delivery_order_item_id')->nullable();
        $table->string('movement_type');
        $table->unsignedBigInteger('warehouse_from_id')->nullable();
        $table->unsignedBigInteger('warehouse_to_id')->nullable();
        $table->string('batch_number')->nullable();
        $table->date('expiry_date')->nullable();
        $table->decimal('quantity', 20, 4)->default(0);
        $table->string('fefo_fifo_rule')->nullable();
        $table->string('reference_type')->nullable();
        $table->unsignedBigInteger('reference_id')->nullable();
        $table->string('idempotency_key', 120)->nullable();
        $table->timestamps();
    });

    Schema::create('currencies', function ($table) {
        $table->id();
        $table->string('currency_name')->nullable();
        $table->timestamps();
    });

    Schema::create('purchase_orders', function ($table) {
        $table->increments('id');
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('warehouse_id')->nullable();
        $table->timestamps();
    });

    Schema::create('purchase_bills', function ($table) {
        $table->increments('id');
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedInteger('purchase_order_id')->nullable();
        $table->timestamps();
    });

    Schema::create('purchase_vendor_credits', function ($table) {
        $table->increments('id');
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedInteger('currency_id');
        $table->unsignedInteger('bill_id')->nullable();
        $table->decimal('sub_total', 16, 2)->default(0);
        $table->double('discount')->default(0);
        $table->string('discount_type')->default('percent');
        $table->decimal('total', 16, 2)->default(0);
        $table->string('calculate_tax')->default('after_discount');
        $table->string('status')->default('open');
        $table->timestamps();
    });

    Schema::create('purchase_vendor_items', function ($table) {
        $table->increments('id');
        $table->unsignedInteger('credit_id')->nullable();
        $table->string('item_name');
        $table->text('item_summary')->nullable();
        $table->string('type')->default('item');
        $table->decimal('quantity', 16, 2);
        $table->decimal('unit_price', 16, 2)->default(0);
        $table->decimal('amount', 16, 2)->default(0);
        $table->unsignedBigInteger('product_id')->nullable();
        $table->unsignedBigInteger('warehouse_id')->nullable();
        $table->unsignedBigInteger('unit_id')->nullable();
        $table->timestamps();
    });

    DB::table('currencies')->insert([
        'id' => 1,
        'currency_name' => 'USD',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('warehouses')->insert([
        'id' => 1,
        'company_id' => 10,
        'name' => 'WH-1',
        'is_default' => true,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('products')->insert([
        'id' => 99,
        'company_id' => 10,
        'name' => 'SKU-99',
        'type' => 'goods',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('warehouse_product_batches')->insert([
        'company_id' => 10,
        'warehouse_id' => 1,
        'product_id' => 99,
        'batch_number' => 'B-01',
        'quantity' => 10,
        'reserved_quantity' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('warehouse_product_stock')->insert([
        'warehouse_id' => 1,
        'product_id' => 99,
        'quantity' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('purchase_orders')->insert([
        'id' => 1,
        'company_id' => 10,
        'warehouse_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('purchase_bills')->insert([
        'id' => 1,
        'company_id' => 10,
        'purchase_order_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

afterEach(function () {
    Schema::dropIfExists('purchase_vendor_items');
    Schema::dropIfExists('purchase_vendor_credits');
    Schema::dropIfExists('purchase_bills');
    Schema::dropIfExists('purchase_orders');
    Schema::dropIfExists('currencies');
    Schema::dropIfExists('stock_movements');
    Schema::dropIfExists('warehouse_product_stock');
    Schema::dropIfExists('warehouse_product_batches');
    Schema::dropIfExists('products');
    Schema::dropIfExists('warehouses');
});

function insertVendorCreditForStockTest(int $billId = 1): int
{
    return (int) DB::table('purchase_vendor_credits')->insertGetId([
        'company_id' => 10,
        'currency_id' => 1,
        'bill_id' => $billId,
        'sub_total' => 0,
        'discount' => 0,
        'discount_type' => 'percent',
        'total' => 0,
        'calculate_tax' => 'after_discount',
        'status' => 'open',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('decreases warehouse stock when a vendor credit product line is created', function () {
    $creditId = insertVendorCreditForStockTest(1);

    PurchaseVendorItem::withoutGlobalScopes()->create([
        'credit_id' => $creditId,
        'item_name' => 'Return',
        'type' => 'item',
        'product_id' => 99,
        'quantity' => 3,
        'unit_price' => 1,
        'amount' => 3,
    ]);

    $qty = (float) DB::table('warehouse_product_stock')
        ->where('warehouse_id', 1)
        ->where('product_id', 99)
        ->value('quantity');

    expect($qty)->toBe(7.0);

    $outbound = DB::table('stock_movements')
        ->where('movement_type', 'outbound')
        ->where('reference_type', PurchaseVendorCredit::class)
        ->where('reference_id', $creditId)
        ->count();

    expect($outbound)->toBeGreaterThanOrEqual(1);
});

it('does not double outbound when posting the same vendor credit line twice', function () {
    $creditId = insertVendorCreditForStockTest(1);

    $line = PurchaseVendorItem::withoutGlobalScopes()->create([
        'credit_id' => $creditId,
        'item_name' => 'Return',
        'type' => 'item',
        'product_id' => 99,
        'quantity' => 2,
        'unit_price' => 1,
        'amount' => 2,
    ]);

    $qtyAfterFirst = (float) DB::table('warehouse_product_stock')
        ->where('warehouse_id', 1)
        ->where('product_id', 99)
        ->value('quantity');

    $svc = app(VendorCreditWarehouseStockService::class);
    $svc->postOutboundForVendorCreditItem($line->fresh());
    $svc->postOutboundForVendorCreditItem($line->fresh());

    $qtyAfterSecond = (float) DB::table('warehouse_product_stock')
        ->where('warehouse_id', 1)
        ->where('product_id', 99)
        ->value('quantity');

    expect($qtyAfterSecond)->toBe($qtyAfterFirst);

    $keys = DB::table('stock_movements')
        ->where('movement_type', 'outbound')
        ->where('idempotency_key', 'like', 'vendor-credit-outbound:'.$creditId.':%')
        ->count();

    expect($keys)->toBe(1);
});

it('reverses outbound stock when the vendor credit header is deleted', function () {
    $creditId = insertVendorCreditForStockTest(1);

    PurchaseVendorItem::withoutGlobalScopes()->create([
        'credit_id' => $creditId,
        'item_name' => 'Return',
        'type' => 'item',
        'product_id' => 99,
        'quantity' => 4,
        'unit_price' => 1,
        'amount' => 4,
    ]);

    $afterOutbound = (float) DB::table('warehouse_product_stock')
        ->where('warehouse_id', 1)
        ->where('product_id', 99)
        ->value('quantity');
    expect($afterOutbound)->toBe(6.0);

    PurchaseVendorCredit::withoutGlobalScopes([CompanyScope::class])->findOrFail($creditId)->delete();

    $afterDelete = (float) DB::table('warehouse_product_stock')
        ->where('warehouse_id', 1)
        ->where('product_id', 99)
        ->value('quantity');
    expect($afterDelete)->toBe(10.0);

    $reversal = DB::table('stock_movements')
        ->where('movement_type', 'inbound')
        ->where('reference_type', 'purchase_vendor_credit_stock_reversal')
        ->where('reference_id', $creditId)
        ->count();
    expect($reversal)->toBeGreaterThanOrEqual(1);
});

it('resyncs stock when vendor credit line quantity is updated', function () {
    $creditId = insertVendorCreditForStockTest(1);

    $line = PurchaseVendorItem::withoutGlobalScopes()->create([
        'credit_id' => $creditId,
        'item_name' => 'Return',
        'type' => 'item',
        'product_id' => 99,
        'quantity' => 2,
        'unit_price' => 1,
        'amount' => 2,
    ]);

    expect((float) DB::table('warehouse_product_stock')->where('warehouse_id', 1)->where('product_id', 99)->value('quantity'))->toBe(8.0);

    $line->quantity = 5;
    $line->amount = 5;
    $line->save();

    expect((float) DB::table('warehouse_product_stock')->where('warehouse_id', 1)->where('product_id', 99)->value('quantity'))->toBe(5.0);
});
