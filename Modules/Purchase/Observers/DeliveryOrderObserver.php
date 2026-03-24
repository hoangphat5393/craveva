<?php

namespace Modules\Purchase\Observers;

use App\Models\DeliveryOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Warehouse\Services\StockMovementService;

/**
 * Inbound DO (purchase receiving note): when status = received, post stock via StockMovementService.
 * Idempotent via delivery_orders.inbound_stock_applied.
 *
 * Double-count risk if both PO "delivered" and DO "received" post stock — configure warehouse.php flags.
 */
class DeliveryOrderObserver
{
    public function saved(DeliveryOrder $deliveryOrder): void
    {
        if (! config('warehouse.inbound_from_delivery_order_received', false)) {
            return;
        }

        if ($deliveryOrder->inbound_stock_applied) {
            return;
        }

        if ($deliveryOrder->status !== 'received') {
            return;
        }

        $type = $deliveryOrder->type ?: 'inbound';
        if ($type !== 'inbound') {
            return;
        }

        if (! class_exists(StockMovementService::class)) {
            return;
        }

        $warehouseId = $deliveryOrder->warehouse_id
            ?? $deliveryOrder->purchaseOrder?->warehouse_id;

        if (! $warehouseId) {
            Log::warning('DeliveryOrder inbound stock skipped: no warehouse_id', [
                'delivery_order_id' => $deliveryOrder->id,
            ]);

            return;
        }

        $deliveryOrder->loadMissing('items');

        $payloads = [];
        foreach ($deliveryOrder->items as $item) {
            $qty = (float) ($item->quantity_received ?? 0);
            if ($qty <= 0 || ! $item->product_id) {
                continue;
            }

            $payloads[] = [
                'company_id' => $deliveryOrder->company_id,
                'warehouse_id' => (int) $warehouseId,
                'product_id' => (int) $item->product_id,
                'quantity' => $qty,
                'batch_number' => null,
                'expiry_date' => null,
                'reference_type' => DeliveryOrder::class,
                'reference_id' => $deliveryOrder->id,
                'delivery_order_item_id' => $item->id,
            ];
        }

        if ($payloads === []) {
            return;
        }

        try {
            app(StockMovementService::class)->recordInboundBatch($payloads);

            DB::table('delivery_orders')
                ->where('id', $deliveryOrder->id)
                ->update([
                    'inbound_stock_applied' => true,
                    'updated_at' => now(),
                ]);
        } catch (\Throwable $e) {
            Log::error('DeliveryOrder inbound stock failed: ' . $e->getMessage(), [
                'delivery_order_id' => $deliveryOrder->id,
            ]);
            throw $e;
        }
    }
}
