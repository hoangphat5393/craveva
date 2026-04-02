<?php

namespace Tests\Unit;

use Tests\TestCase;

class WarehousePhase3ConfigTest extends TestCase
{
    public function test_phase3_inbound_flags_have_safe_defaults(): void
    {
        $this->assertIsBool(config('warehouse.inbound_from_purchase_order_delivered'));
        $this->assertIsBool(config('warehouse.inbound_from_delivery_order_received'));
        $this->assertContains(config('warehouse.sales_outbound_mode'), ['shipment', 'invoice']);
    }

    public function test_stock_movement_service_has_record_inbound_batch(): void
    {
        $this->assertTrue(method_exists(
            \Modules\Warehouse\Services\StockMovementService::class,
            'recordInboundBatch'
        ));
    }

    public function test_stock_reservation_service_is_registered(): void
    {
        $this->assertInstanceOf(
            \Modules\Warehouse\Services\StockReservationService::class,
            app(\Modules\Warehouse\Services\StockReservationService::class)
        );
    }
}
