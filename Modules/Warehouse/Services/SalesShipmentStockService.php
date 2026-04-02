<?php

namespace Modules\Warehouse\Services;

use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Modules\Purchase\Entities\SalesShipment;
use Modules\Purchase\Support\SalesDoRuntime;

class SalesShipmentStockService
{
    public function __construct(
        protected StockMovementService $stockMovementService,
        protected StockReservationService $stockReservationService,
        protected WarehouseFlowPolicyService $flowPolicy
    ) {}

    /**
     * In Option B, outbound can be posted from shipment (mode "shipment").
     * This method is idempotent via sales_shipments.outbound_stock_applied.
     */
    public function applyOutboundForShipment($shipment): void
    {
        if (! $this->shouldPostOutboundFromShipment()) {
            return;
        }

        DB::transaction(function () use ($shipment) {
            $headerModelClass = SalesDoRuntime::headerModelClass();
            $locked = $headerModelClass::query()
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
                    'unit_id' => $item->unit_id ? (int) $item->unit_id : null,
                    'batch_number' => $item->batch_number,
                    'expiry_date' => null,
                    'reference_type' => get_class($locked),
                    'reference_id' => $locked->id,
                    'idempotency_key' => 'sales-do-outbound:' . $locked->id . ':' . $item->id,
                ]);
            }

            $this->stockReservationService->consumeByReference(get_class($locked), (int) $locked->id);
            $locked->outbound_stock_applied = true;
            $locked->save();
        });
    }

    /**
     * Reverse outbound movements created from a shipment.
     * Safe no-op when outbound was never applied.
     */
    public function reverseOutboundForShipment($shipment): void
    {
        if (! $this->shouldPostOutboundFromShipment()) {
            return;
        }

        DB::transaction(function () use ($shipment) {
            $headerModelClass = SalesDoRuntime::headerModelClass();
            $locked = $headerModelClass::query()->lockForUpdate()->findOrFail($shipment->id);

            if (! $locked->outbound_stock_applied) {
                return;
            }

            $movements = StockMovement::query()
                ->whereIn('reference_type', [get_class($locked), SalesShipment::class])
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
                    'unit_id' => $movement->unit_id ? (int) $movement->unit_id : null,
                    'batch_number' => $movement->batch_number,
                    'expiry_date' => $movement->expiry_date,
                    'reference_type' => 'sales_shipment_stock_reversal',
                    'reference_id' => $locked->id,
                    'idempotency_key' => 'sales-do-reversal:' . $locked->id . ':' . $movement->id,
                ]);
            }

            $locked->outbound_stock_applied = false;
            $locked->save();
        });
    }

    public function shouldPostOutboundFromShipment(): bool
    {
        $this->flowPolicy->assertOutboundConfigurationValid();

        return config('warehouse.sales_outbound_mode', 'shipment') === 'shipment';
    }

    public function ensureReservationsForShipment($shipment): void
    {
        DB::transaction(function () use ($shipment) {
            $headerModelClass = SalesDoRuntime::headerModelClass();
            $locked = $headerModelClass::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($shipment->id);

            $referenceType = get_class($locked);
            if ($this->stockReservationService->hasActiveReservations($referenceType, (int) $locked->id)) {
                return;
            }

            foreach ($locked->items as $item) {
                $qty = (float) $item->quantity_shipped;
                if ($qty <= 0 || ! $item->product_id) {
                    continue;
                }

                $this->stockReservationService->reserve([
                    'company_id' => (int) $locked->company_id,
                    'warehouse_id' => (int) $locked->warehouse_id,
                    'product_id' => (int) $item->product_id,
                    'quantity' => $qty,
                    'unit_id' => $item->unit_id ? (int) $item->unit_id : null,
                    'batch_number' => $item->batch_number,
                    'expiry_date' => null,
                    'reference_type' => $referenceType,
                    'reference_id' => (int) $locked->id,
                ]);
            }
        });
    }

    public function releaseReservationsForShipment($shipment): void
    {
        $headerModelClass = SalesDoRuntime::headerModelClass();
        $referenceType = $headerModelClass;
        $this->stockReservationService->releaseByReference($referenceType, (int) $shipment->id);
    }
}
