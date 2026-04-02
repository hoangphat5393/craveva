<?php

namespace Tests\Unit;

use Illuminate\Support\Collection;
use Modules\Warehouse\Services\StockMovementService;
use Modules\Warehouse\Exceptions\WarehouseBusinessException;
use Tests\TestCase;

class StockMovementServiceTest extends TestCase
{
    public function test_negative_stock_is_blocked_by_default(): void
    {
        $service = app(StockMovementService::class);

        $this->expectException(WarehouseBusinessException::class);
        $service->guardStockNotNegative(5, 10, false);
    }

    public function test_negative_stock_can_be_allowed_by_override(): void
    {
        $service = app(StockMovementService::class);
        $service->guardStockNotNegative(5, 10, true);

        $this->assertTrue(true);
    }

    public function test_fefo_sort_places_earliest_expiry_first(): void
    {
        $service = app(StockMovementService::class);
        $rows = new Collection([
            (object) ['id' => 3, 'expiration_date' => null],
            (object) ['id' => 1, 'expiration_date' => '2026-05-10'],
            (object) ['id' => 2, 'expiration_date' => '2026-04-01'],
        ]);

        $sorted = $service->sortForFefo($rows);

        $this->assertSame(2, $sorted[0]->id);
        $this->assertSame(1, $sorted[1]->id);
        $this->assertSame(3, $sorted[2]->id);
    }
}
