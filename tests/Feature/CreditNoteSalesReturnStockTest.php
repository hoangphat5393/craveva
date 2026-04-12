<?php

use App\Models\CreditNoteItem;
use App\Models\CreditNotes;
use App\Models\Invoice;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Entities\SalesDo;
use Modules\Purchase\Entities\SalesDoItem;
use Modules\Warehouse\Contracts\SalesReturnInboundGateInterface;
use Modules\Warehouse\Services\CreditNoteWarehouseStockService;
use Modules\Warehouse\Services\InvoiceWarehouseStockService;
use Modules\Warehouse\Services\SalesShipmentStockService;
use Modules\Warehouse\Services\StockMovementService;
use Modules\Warehouse\Services\WarehouseFlowConfigService;
use Modules\Warehouse\Services\WarehouseFlowPolicyService;

beforeEach(function () {
    $stockMovement = app(StockMovementService::class);
    $flowPolicy = app(WarehouseFlowPolicyService::class);
    $flowConfig = app(WarehouseFlowConfigService::class);

    $invoiceStockStub = new class($stockMovement, $flowPolicy, $flowConfig) extends InvoiceWarehouseStockService
    {
        public function isEnabled(?int $companyId = null): bool
        {
            return true;
        }
    };

    $this->app->instance(InvoiceWarehouseStockService::class, $invoiceStockStub);
    $this->app->instance(CreditNoteWarehouseStockService::class, new CreditNoteWarehouseStockService(
        $stockMovement,
        $invoiceStockStub,
        $flowConfig,
        app(SalesReturnInboundGateInterface::class),
    ));

    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    Config::set('purchase.do_grn_cutover_enabled', false);
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Config::set('warehouse.sales_outbound_enabled', true);
    Config::set('warehouse.sales_outbound_mode', 'shipment');
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

    Schema::create('stock_reservations', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('warehouse_id');
        $table->unsignedBigInteger('product_id');
        $table->string('batch_number')->nullable();
        $table->date('expiry_date')->nullable();
        $table->decimal('reserved_quantity', 20, 4)->default(0);
        $table->string('reference_type')->nullable();
        $table->unsignedBigInteger('reference_id')->nullable();
        $table->string('status', 20)->default('active');
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

    Schema::create('sales_dos', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->unsignedBigInteger('order_id')->nullable();
        $table->unsignedBigInteger('warehouse_id')->nullable();
        $table->string('do_number')->nullable();
        $table->date('do_date')->nullable();
        $table->string('status')->default('draft');
        $table->boolean('outbound_stock_applied')->default(false);
        $table->text('notes')->nullable();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->timestamps();
    });

    Schema::create('sales_do_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('sales_do_id');
        $table->unsignedBigInteger('order_item_id')->nullable();
        $table->unsignedBigInteger('product_id')->nullable();
        $table->decimal('quantity_ordered', 20, 4)->default(0);
        $table->decimal('quantity_shipped', 20, 4)->default(0);
        $table->unsignedBigInteger('unit_id')->nullable();
        $table->string('batch_number')->nullable();
        $table->timestamps();
    });

    Schema::create('invoices', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('order_id')->nullable();
        $table->unsignedInteger('client_id')->nullable();
        $table->string('status')->default('paid');
        $table->boolean('credit_note')->default(false);
        $table->timestamps();
    });

    Schema::create('currencies', function ($table) {
        $table->id();
        $table->string('currency_name')->nullable();
        $table->timestamps();
    });

    Schema::create('credit_notes', function ($table) {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->unsignedBigInteger('invoice_id')->nullable();
        $table->string('cn_number')->nullable();
        $table->date('issue_date')->nullable();
        $table->date('due_date')->nullable();
        $table->decimal('sub_total', 12, 2)->default(0);
        $table->decimal('discount', 12, 2)->default(0);
        $table->string('discount_type')->default('percent');
        $table->decimal('total', 12, 2)->default(0);
        $table->unsignedInteger('currency_id')->nullable();
        $table->string('status')->default('open');
        $table->timestamps();
    });

    Schema::create('credit_note_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('credit_note_id');
        $table->string('item_name')->default('x');
        $table->string('type')->default('item');
        $table->unsignedBigInteger('product_id')->nullable();
        $table->unsignedBigInteger('warehouse_id')->nullable();
        $table->decimal('quantity', 20, 4)->default(0);
        $table->decimal('unit_price', 12, 2)->default(0);
        $table->decimal('amount', 12, 2)->default(0);
        $table->timestamps();
    });

    Schema::create('credit_note_item_images', function ($table) {
        $table->id();
        $table->unsignedBigInteger('credit_note_item_id');
        $table->timestamps();
    });

    Schema::create('universal_search', function ($table) {
        $table->id();
        $table->unsignedBigInteger('searchable_id')->nullable();
        $table->string('module_type')->nullable();
        $table->timestamps();
    });

    Schema::create('notifications', function ($table) {
        $table->uuid('id')->primary();
        $table->string('type')->nullable();
        $table->string('notifiable_type')->nullable();
        $table->unsignedBigInteger('notifiable_id')->nullable();
        $table->text('data')->nullable();
        $table->timestamp('read_at')->nullable();
        $table->timestamps();
    });

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

    DB::table('invoices')->insert([
        'id' => 1,
        'company_id' => 10,
        'order_id' => 1001,
        'client_id' => null,
        'status' => 'paid',
        'credit_note' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('currencies')->insert([
        'id' => 1,
        'currency_name' => 'USD',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

afterEach(function () {
    Schema::dropIfExists('notifications');
    Schema::dropIfExists('universal_search');
    Schema::dropIfExists('credit_note_item_images');
    Schema::dropIfExists('credit_note_items');
    Schema::dropIfExists('credit_notes');
    Schema::dropIfExists('currencies');
    Schema::dropIfExists('invoices');
    Schema::dropIfExists('sales_do_items');
    Schema::dropIfExists('sales_dos');
    Schema::dropIfExists('stock_reservations');
    Schema::dropIfExists('stock_movements');
    Schema::dropIfExists('warehouse_product_stock');
    Schema::dropIfExists('warehouse_product_batches');
    Schema::dropIfExists('products');
    Schema::dropIfExists('warehouses');
});

it('increases warehouse stock when a credit note line is created after shipment outbound (shipment mode)', function () {
    $shipment = SalesDo::create([
        'company_id' => 10,
        'order_id' => 1001,
        'warehouse_id' => 1,
        'do_number' => 'DO-000001',
        'do_date' => now()->toDateString(),
        'status' => 'confirmed',
    ]);

    SalesDoItem::create([
        'sales_do_id' => $shipment->id,
        'order_item_id' => 501,
        'product_id' => 99,
        'quantity_ordered' => 10,
        'quantity_shipped' => 8,
    ]);

    app(SalesShipmentStockService::class)->applyOutboundForShipment($shipment);

    $afterShip = (float) DB::table('warehouse_product_stock')
        ->where('warehouse_id', 1)
        ->where('product_id', 99)
        ->value('quantity');
    expect($afterShip)->toBe(2.0);

    $creditNoteId = DB::table('credit_notes')->insertGetId([
        'company_id' => 10,
        'invoice_id' => 1,
        'cn_number' => 'CN-001',
        'issue_date' => now()->toDateString(),
        'due_date' => now()->toDateString(),
        'sub_total' => 0,
        'discount' => 0,
        'discount_type' => 'percent',
        'total' => 0,
        'currency_id' => 1,
        'status' => 'open',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    CreditNoteItem::create([
        'credit_note_id' => $creditNoteId,
        'item_name' => 'Returned',
        'type' => 'item',
        'product_id' => 99,
        'quantity' => 3,
        'unit_price' => 1,
        'amount' => 3,
    ]);

    $afterCn = (float) DB::table('warehouse_product_stock')
        ->where('warehouse_id', 1)
        ->where('product_id', 99)
        ->value('quantity');
    expect($afterCn)->toBe(5.0);

    $inbound = DB::table('stock_movements')
        ->where('movement_type', 'inbound')
        ->where('reference_type', CreditNotes::class)
        ->where('reference_id', $creditNoteId)
        ->count();
    expect($inbound)->toBe(1);
});

it('does not post invoice outbound movements in shipment mode when invoice is synced with credit_note flag', function () {
    $invoice = Invoice::query()->findOrFail(1);
    $before = DB::table('stock_movements')
        ->where('reference_type', Invoice::class)
        ->count();

    app(InvoiceWarehouseStockService::class)->syncInvoiceStock($invoice->fresh());
    $invoice->credit_note = true;
    $invoice->saveQuietly();
    app(InvoiceWarehouseStockService::class)->syncInvoiceStock($invoice->fresh());

    $after = DB::table('stock_movements')
        ->where('reference_type', Invoice::class)
        ->count();
    expect($after)->toBe($before);
});

it('does not double inbound when posting the same credit note line twice', function () {
    $shipment = SalesDo::create([
        'company_id' => 10,
        'order_id' => 1001,
        'warehouse_id' => 1,
        'do_number' => 'DO-000002',
        'do_date' => now()->toDateString(),
        'status' => 'confirmed',
    ]);

    SalesDoItem::create([
        'sales_do_id' => $shipment->id,
        'order_item_id' => 502,
        'product_id' => 99,
        'quantity_ordered' => 10,
        'quantity_shipped' => 5,
    ]);

    app(SalesShipmentStockService::class)->applyOutboundForShipment($shipment);

    $creditNoteId = DB::table('credit_notes')->insertGetId([
        'company_id' => 10,
        'invoice_id' => 1,
        'cn_number' => 'CN-002',
        'issue_date' => now()->toDateString(),
        'due_date' => now()->toDateString(),
        'sub_total' => 0,
        'discount' => 0,
        'discount_type' => 'percent',
        'total' => 0,
        'currency_id' => 1,
        'status' => 'open',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $line = CreditNoteItem::create([
        'credit_note_id' => $creditNoteId,
        'item_name' => 'Returned',
        'type' => 'item',
        'product_id' => 99,
        'quantity' => 1,
        'unit_price' => 1,
        'amount' => 1,
    ]);

    $qtyAfterFirst = (float) DB::table('warehouse_product_stock')
        ->where('warehouse_id', 1)
        ->where('product_id', 99)
        ->value('quantity');

    $svc = app(CreditNoteWarehouseStockService::class);
    $svc->postInboundForCreditNoteItem($line->fresh());
    $svc->postInboundForCreditNoteItem($line->fresh());

    $qtyAfterSecond = (float) DB::table('warehouse_product_stock')
        ->where('warehouse_id', 1)
        ->where('product_id', 99)
        ->value('quantity');

    expect($qtyAfterSecond)->toBe($qtyAfterFirst);

    $inboundRows = DB::table('stock_movements')
        ->where('movement_type', 'inbound')
        ->where('reference_type', CreditNotes::class)
        ->where('reference_id', $creditNoteId)
        ->count();
    expect($inboundRows)->toBe(1);
});

it('reverses inbound stock when the credit note is deleted', function () {
    $shipment = SalesDo::create([
        'company_id' => 10,
        'order_id' => 1001,
        'warehouse_id' => 1,
        'do_number' => 'DO-000003',
        'do_date' => now()->toDateString(),
        'status' => 'confirmed',
    ]);

    SalesDoItem::create([
        'sales_do_id' => $shipment->id,
        'order_item_id' => 503,
        'product_id' => 99,
        'quantity_ordered' => 10,
        'quantity_shipped' => 4,
    ]);

    app(SalesShipmentStockService::class)->applyOutboundForShipment($shipment);

    $creditNoteId = DB::table('credit_notes')->insertGetId([
        'company_id' => 10,
        'invoice_id' => 1,
        'cn_number' => 'CN-003',
        'issue_date' => now()->toDateString(),
        'due_date' => now()->toDateString(),
        'sub_total' => 0,
        'discount' => 0,
        'discount_type' => 'percent',
        'total' => 0,
        'currency_id' => 1,
        'status' => 'open',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    CreditNoteItem::create([
        'credit_note_id' => $creditNoteId,
        'item_name' => 'Returned',
        'type' => 'item',
        'product_id' => 99,
        'quantity' => 2,
        'unit_price' => 1,
        'amount' => 2,
    ]);

    $afterInbound = (float) DB::table('warehouse_product_stock')
        ->where('warehouse_id', 1)
        ->where('product_id', 99)
        ->value('quantity');
    expect($afterInbound)->toBe(8.0);

    CreditNotes::withoutGlobalScopes()->findOrFail($creditNoteId)->delete();

    $afterDelete = (float) DB::table('warehouse_product_stock')
        ->where('warehouse_id', 1)
        ->where('product_id', 99)
        ->value('quantity');
    expect($afterDelete)->toBe(6.0);

    $reversal = DB::table('stock_movements')
        ->where('movement_type', 'outbound')
        ->where('reference_type', 'credit_note_stock_reversal')
        ->where('reference_id', $creditNoteId)
        ->count();
    expect($reversal)->toBe(1);
});
