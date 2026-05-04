<?php

namespace Modules\Purchase\Services;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Support\SalesDoRuntime;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Services\SalesShipmentStockService;

class SalesDoService
{
    public function __construct(
        protected SalesShipmentStockService $stockService
    ) {}

    public function confirm(Model $shipment): ?string
    {
        if ($shipment->status !== 'draft') {
            return 'messages.invalidRequest';
        }

        if ($shipment->items->isEmpty()) {
            return 'messages.addItem';
        }

        if ($message = $this->blockingMessageForInvalidShipmentHeader($shipment)) {
            return $message;
        }

        DB::transaction(function () use ($shipment) {
            $this->stockService->ensureReservationsForShipment($shipment);

            $shipment->status = 'confirmed';
            $shipment->updated_by = user()->id;
            $shipment->save();
        });

        return null;
    }

    public function create(array $payload, ?int $companyId, int $userId): Model
    {
        return DB::transaction(function () use ($payload, $companyId, $userId) {
            $headerModelClass = SalesDoRuntime::headerModelClass();
            $shipment = new $headerModelClass;
            $shipment->company_id = $companyId;
            $shipment->order_id = (int) $payload['order_id'];
            $shipment->warehouse_id = (int) $payload['warehouse_id'];
            $shipment->shipment_number = $payload['shipment_number'];
            $shipment->shipment_date = $payload['shipment_date'];
            $shipment->status = $payload['status'];
            $shipment->notes = $payload['notes'];
            $shipment->created_by = $userId;
            $shipment->updated_by = $userId;
            $shipment->save();

            $this->syncItems($shipment, $payload);

            if ($payload['status'] === 'confirmed') {
                $this->stockService->ensureReservationsForShipment($shipment->fresh(['items']));
            }

            return $shipment;
        });
    }

    public function update(Model $shipment, array $payload, int $userId): Model
    {
        return DB::transaction(function () use ($shipment, $payload, $userId) {
            $priorStatus = $shipment->status;

            $shipment->order_id = (int) $payload['order_id'];
            $shipment->warehouse_id = (int) $payload['warehouse_id'];
            $shipment->shipment_number = $payload['shipment_number'];
            $shipment->shipment_date = $payload['shipment_date'];
            $shipment->status = $payload['status'];
            $shipment->notes = $payload['notes'];
            $shipment->updated_by = $userId;
            $shipment->save();

            $this->syncItems($shipment, $payload);

            if ($priorStatus === 'draft' && $payload['status'] === 'confirmed') {
                $this->stockService->ensureReservationsForShipment($shipment->fresh(['items']));
            }

            return $shipment;
        });
    }

    public function ship(Model $shipment): ?string
    {
        if (! in_array($shipment->status, ['confirmed', 'draft'], true)) {
            return 'messages.invalidRequest';
        }

        if ($message = $this->blockingMessageForInvalidShipmentHeader($shipment)) {
            return $message;
        }

        if ($shipment->items->sum('quantity_shipped') <= 0) {
            return 'messages.salesDoShipQuantityRequired';
        }

        DB::transaction(function () use ($shipment) {
            $this->stockService->ensureReservationsForShipment($shipment);

            $shipment->status = 'shipped';
            $shipment->updated_by = user()->id;
            $shipment->save();

            $this->stockService->applyOutboundForShipment($shipment);
        });

        return null;
    }

    public function deliver(Model $shipment): ?string
    {
        if ($shipment->status !== 'shipped') {
            return 'messages.invalidRequest';
        }

        $shipment->status = 'delivered';
        $shipment->updated_by = user()->id;
        $shipment->save();

        return null;
    }

    public function reverse(Model $shipment): ?string
    {
        if (! in_array($shipment->status, ['shipped', 'delivered'], true)) {
            return 'messages.invalidRequest';
        }

        DB::transaction(function () use ($shipment) {
            $this->stockService->reverseOutboundForShipment($shipment);
            $this->stockService->ensureReservationsForShipment($shipment);
            $shipment->status = 'confirmed';
            $shipment->updated_by = user()->id;
            $shipment->save();
        });

        return null;
    }

    public function cancel(Model $shipment): ?string
    {
        if ($shipment->status === 'cancelled') {
            return null;
        }

        DB::transaction(function () use ($shipment) {
            if ($shipment->outbound_stock_applied) {
                $this->stockService->reverseOutboundForShipment($shipment);
            }
            $this->stockService->releaseReservationsForShipment($shipment);

            $shipment->status = 'cancelled';
            $shipment->updated_by = user()->id;
            $shipment->save();
        });

        return null;
    }

    /**
     * @return string|null Translation key when the shipment header cannot support stock operations.
     */
    protected function blockingMessageForInvalidShipmentHeader(Model $shipment): ?string
    {
        if ((int) ($shipment->order_id ?? 0) < 1 || (int) ($shipment->warehouse_id ?? 0) < 1) {
            return 'messages.salesDoHeaderRequiresOrderAndWarehouse';
        }

        if (Schema::hasTable('orders')) {
            $orderQuery = Order::withoutGlobalScopes()->whereKey((int) $shipment->order_id);
            $companyId = (int) ($shipment->company_id ?? 0);
            if ($companyId > 0) {
                $orderQuery->where('company_id', $companyId);
            }
            if (! $orderQuery->exists()) {
                return 'messages.salesDoHeaderOrderNotFoundForCompany';
            }
        }

        if (Schema::hasTable('warehouses') && class_exists(Warehouse::class)) {
            $warehouseQuery = Warehouse::withoutGlobalScopes()->whereKey((int) $shipment->warehouse_id);
            $companyId = (int) ($shipment->company_id ?? 0);
            if ($companyId > 0) {
                $warehouseQuery->where('company_id', $companyId);
            }
            if (! $warehouseQuery->exists()) {
                return 'messages.salesDoHeaderWarehouseNotFoundForCompany';
            }
        }

        return null;
    }

    protected function syncItems(Model $shipment, array $payload): void
    {
        $itemModelClass = SalesDoRuntime::itemModelClass();
        $itemForeignKey = SalesDoRuntime::itemForeignKey();
        $itemModelClass::query()->where($itemForeignKey, $shipment->id)->delete();

        foreach ($payload['order_item_id'] as $idx => $orderItemId) {
            $itemModelClass::create([
                $itemForeignKey => $shipment->id,
                'order_item_id' => (int) $orderItemId,
                'product_id' => isset($payload['product_id'][$idx]) ? (int) $payload['product_id'][$idx] : null,
                'quantity_ordered' => (float) ($payload['quantity_ordered'][$idx] ?? 0),
                'quantity_shipped' => (float) ($payload['quantity_shipped'][$idx] ?? 0),
                'unit_id' => isset($payload['unit_id'][$idx]) ? (int) $payload['unit_id'][$idx] : null,
                'warehouse_batch_id' => isset($payload['warehouse_batch_id'][$idx]) ? (int) $payload['warehouse_batch_id'][$idx] : null,
                'batch_number' => $payload['batch_number'][$idx] ?? null,
                'expiration_date' => $payload['expiration_date'][$idx] ?? null,
            ]);
        }
    }
}
