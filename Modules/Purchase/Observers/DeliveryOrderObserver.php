<?php

namespace Modules\Purchase\Observers;

use App\Models\DeliveryOrder;
use App\Models\Grn;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Purchase\Support\GrnRuntime;
use Modules\Warehouse\Services\StockMovementService;
use Modules\Warehouse\Services\WarehouseFlowConfigService;
use Modules\Warehouse\Services\WarehouseFlowPolicyService;

/**
 * Inbound DO (purchase receiving note): when status = received, post stock via StockMovementService.
 * Idempotent via delivery_orders.inbound_stock_applied.
 *
 * Double-count risk if both PO "delivered" and DO "received" post stock — configure warehouse.php flags.
 */
class DeliveryOrderObserver
{
    public function saved(DeliveryOrder|Grn $deliveryOrder): void
    {
        $companyId = (int) $deliveryOrder->company_id;
        if ($companyId <= 0) {
            return;
        }

        $flowConfig = app(WarehouseFlowConfigService::class);

        if (! $flowConfig->inboundFromDeliveryOrderReceived($companyId)) {
            return;
        }

        // Safety guard: if both inbound channels are enabled and the linked PO is already delivered,
        // skip DO inbound posting to avoid double-counting the same receiving event. This must run
        // before assertInboundSourceAllowed(), which forbids both flags globally for active posting.
        if (
            $flowConfig->inboundFromPurchaseOrderDelivered($companyId)
            && $deliveryOrder->purchaseOrder
            && $deliveryOrder->purchaseOrder->delivery_status === 'delivered'
        ) {
            Log::warning('DeliveryOrder inbound stock skipped: PO inbound already eligible (double-count prevention)', [
                'delivery_order_id' => $deliveryOrder->id,
                'purchase_order_id' => $deliveryOrder->purchase_order_id,
            ]);

            return;
        }

        app(WarehouseFlowPolicyService::class)->assertInboundSourceAllowed('delivery_order', $companyId);

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

        $deliveryOrder->loadMissing('items.purchaseItem');

        $payloads = [];
        foreach ($deliveryOrder->items as $item) {
            $qty = (float) ($item->quantity_received ?? 0);
            if ($qty <= 0 || ! $item->product_id) {
                continue;
            }

            $batch = $item->batch_number;
            $batch = ($batch !== null && trim((string) $batch) !== '') ? trim((string) $batch) : null;

            $expiry = null;
            if ($item->expiry_date) {
                $expiry = $item->expiry_date instanceof Carbon
                    ? $item->expiry_date->format('Y-m-d')
                    : (string) $item->expiry_date;
            }

            $rule = $item->picking_rule_applied;
            $rule = in_array($rule, ['FIFO', 'FEFO'], true) ? $rule : null;

            $payloads[] = [
                'company_id' => $deliveryOrder->company_id,
                'warehouse_id' => (int) $warehouseId,
                'product_id' => (int) $item->product_id,
                'quantity' => $qty,
                'unit_id' => $item->purchaseItem?->unit_id ? (int) $item->purchaseItem->unit_id : null,
                'batch_number' => $batch,
                'expiry_date' => $expiry,
                'fefo_fifo_rule' => $rule,
                'reference_type' => get_class($deliveryOrder),
                'reference_id' => $deliveryOrder->id,
                'delivery_order_item_id' => $item->id,
                'idempotency_key' => 'delivery-order-inbound:'.$deliveryOrder->id.':'.$item->id,
            ];
        }

        if ($payloads === []) {
            return;
        }

        try {
            app(StockMovementService::class)->recordInboundBatch($payloads);

            DB::table(GrnRuntime::headerTable())
                ->where('id', $deliveryOrder->id)
                ->update([
                    'inbound_stock_applied' => true,
                    'updated_at' => now(),
                ]);
        } catch (\Throwable $e) {
            Log::error('DeliveryOrder inbound stock failed: '.$e->getMessage(), [
                'delivery_order_id' => $deliveryOrder->id,
            ]);
            throw $e;
        }
    }
}
