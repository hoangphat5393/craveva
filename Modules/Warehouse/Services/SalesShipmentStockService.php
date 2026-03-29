<?php

namespace Modules\Warehouse\Services;

use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Modules\Purchase\Entities\SalesShipment;

class SalesShipmentStockService
{
    public function __construct(
        protected StockMovementService $stockMovementService
    ) {}

    /**
     * In Option B, outbound can be posted from shipment (mode "shipment").
     * This method is idempotent via sales_shipments.outbound_stock_applied.
     */
    public function applyOutboundForShipment(SalesShipment $shipment): void
    {
        if (! $this->shouldPostOutboundFromShipment()) {
            return;
        }

        DB::transaction(function () use ($shipment) {
            $locked = SalesShipment::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($shipment->id);

            if ($locked->outbound_stock_applied) {
                return;
            }

            foreach ($locked->items as $item) {
                $qty = (float) $item->quantity_shipped;
                if ($qty <= 0 || ! $item->product_id) {
                    continue;
                }

                $this->stockMovementService->recordOutbound([
                    'company_id' => (int) $locked->company_id,
                    'warehouse_id' => (int) $locked->warehouse_id,
                    'product_id' => (int) $item->product_id,
                    'quantity' => $qty,
                    'batch_number' => $item->batch_number,
                    'expiry_date' => null,
                    'reference_type' => SalesShipment::class,
                    'reference_id' => $locked->id,
                ]);
            }

            $locked->outbound_stock_applied = true;
            $locked->save();
        });
    }

    /**
     * Reverse outbound movements created from a shipment.
     * Safe no-op when outbound was never applied.
     */
    public function reverseOutboundForShipment(SalesShipment $shipment): void
    {
        if (! $this->shouldPostOutboundFromShipment()) {
            return;
        }

        DB::transaction(function () use ($shipment) {
            $locked = SalesShipment::query()->lockForUpdate()->findOrFail($shipment->id);

            if (! $locked->outbound_stock_applied) {
                return;
            }

            $movements = StockMovement::query()
                ->where('reference_type', SalesShipment::class)
                ->where('reference_id', $locked->id)
                ->where('movement_type', 'outbound')
                ->lockForUpdate()
                ->get();

            foreach ($movements as $movement) {
                $warehouseId = (int) ($movement->warehouse_from_id ?: $locked->warehouse_id);
                $qty = (float) $movement->quantity;
                if ($warehouseId <= 0 || $qty <= 0 || ! $movement->product_id) {
                    continue;
                }

                $this->stockMovementService->recordInbound([
                    'company_id' => (int) $locked->company_id,
                    'warehouse_id' => $warehouseId,
                    'product_id' => (int) $movement->product_id,
                    'quantity' => $qty,
                    'batch_number' => $movement->batch_number,
                    'expiry_date' => $movement->expiry_date,
                    'reference_type' => 'sales_shipment_stock_reversal',
                    'reference_id' => $locked->id,
                ]);
            }

            $locked->outbound_stock_applied = false;
            $locked->save();
        });
    }

    public function shouldPostOutboundFromShipment(): bool
    {
        return config('warehouse.sales_outbound_mode', 'invoice') === 'shipment';
    }
}
