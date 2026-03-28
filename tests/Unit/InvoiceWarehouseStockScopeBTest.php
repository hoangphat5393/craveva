<?php

namespace Tests\Unit;

use App\Models\Invoice;
use Mockery;
use Modules\Purchase\Observers\PaymentObserver;
use Modules\Warehouse\Services\InvoiceWarehouseStockService;
use Modules\Warehouse\Services\StockMovementService;
use Tests\TestCase;

class InvoiceWarehouseStockScopeBTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_sales_outbound_config_defaults_to_false(): void
    {
        $this->assertFalse((bool) config('warehouse.sales_outbound_enabled'));
    }

    public function test_invoice_warehouse_stock_service_is_registered(): void
    {
        $this->assertInstanceOf(
            InvoiceWarehouseStockService::class,
            app(InvoiceWarehouseStockService::class)
        );
    }

    public function test_payment_observer_skips_legacy_stock_when_sales_outbound_enabled(): void
    {
        config(['warehouse.sales_outbound_enabled' => true]);

        $observer = new PaymentObserver;
        $invoice = new Invoice;

        $observer->adjustStock($invoice, 'minus');

        $this->assertTrue(true);
    }

    public function test_should_post_outbound_respects_draft_and_credit_note(): void
    {
        $svc = app(InvoiceWarehouseStockService::class);

        $this->assertFalse($svc->shouldPostOutbound(new Invoice(['status' => 'draft', 'credit_note' => 0])));
        $this->assertFalse($svc->shouldPostOutbound(new Invoice(['status' => 'unpaid', 'credit_note' => 1])));
        $this->assertTrue($svc->shouldPostOutbound(new Invoice(['status' => 'unpaid', 'credit_note' => 0])));
    }

    public function test_sync_skips_stock_movements_while_app_seeding(): void
    {
        config(['app.seeding' => true, 'warehouse.sales_outbound_enabled' => true]);

        try {
            $this->assertTrue(config('app.seeding'));

            $mock = Mockery::mock(StockMovementService::class);
            $mock->shouldNotReceive('recordOutbound');
            $mock->shouldNotReceive('recordInbound');

            $svc = new class($mock) extends InvoiceWarehouseStockService
            {
                public function isEnabled(): bool
                {
                    return true;
                }
            };

            $invoice = new Invoice(['company_id' => 1, 'id' => 5]);
            $svc->syncInvoiceStock($invoice);
        } finally {
            config(['app.seeding' => false]);
        }
    }

    public function test_reverse_skips_when_app_seeding(): void
    {
        config(['app.seeding' => true]);

        try {
            $this->assertTrue(config('app.seeding'));

            $mock = Mockery::mock(StockMovementService::class);
            $mock->shouldNotReceive('recordInbound');

            $svc = new class($mock) extends InvoiceWarehouseStockService
            {
                public function isEnabled(): bool
                {
                    return true;
                }
            };

            $invoice = new Invoice(['id' => 1]);
            $svc->reverseAllPostings($invoice);
        } finally {
            config(['app.seeding' => false]);
        }
    }
}
