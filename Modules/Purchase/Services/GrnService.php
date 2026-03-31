<?php

namespace Modules\Purchase\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Purchase\Support\GrnRuntime;

class GrnService
{
    public function changeStatus(Model $delivery, string $status): ?string
    {
        if (! in_array($status, ['draft', 'inbound', 'received'], true)) {
            return 'messages.invalidRequest';
        }

        $delivery->status = $status;
        $delivery->save();

        return null;
    }

    public function create(array $payload, ?int $companyId): Model
    {
        return DB::transaction(function () use ($payload, $companyId) {
            $headerModelClass = GrnRuntime::headerModelClass();
            $delivery = new $headerModelClass;
            $delivery->company_id = $companyId;
            $this->fillDelivery($delivery, $payload);
            $delivery->save();

            $this->syncItems($delivery, $payload);

            return $delivery;
        });
    }

    public function update(Model $delivery, array $payload): Model
    {
        return DB::transaction(function () use ($delivery, $payload) {
            $this->fillDelivery($delivery, $payload);
            $delivery->save();

            $this->syncItems($delivery, $payload, true);

            return $delivery;
        });
    }

    protected function fillDelivery(Model $delivery, array $payload): void
    {
        $delivery->purchase_order_id = $payload['purchase_order_id'] ?? null;
        $delivery->warehouse_id = $payload['warehouse_id'] ?? null;
        $delivery->type = $payload['type'] ?? 'inbound';
        $delivery->delivery_number = $payload['delivery_number'];
        $delivery->delivery_date = $payload['delivery_date'];
        $delivery->status = $payload['status'];
        $delivery->erp_shipment_reference = $payload['erp_shipment_reference'] ?? null;
        $delivery->wms_shipment_reference = $payload['wms_shipment_reference'] ?? null;
        $delivery->delivery_fee = $payload['delivery_fee'] ?? null;
    }

    protected function syncItems(Model $delivery, array $payload, bool $replace = false): void
    {
        if (! isset($payload['item_id']) || ! is_array($payload['item_id'])) {
            return;
        }

        $itemModelClass = GrnRuntime::itemModelClass();
        $itemForeignKey = GrnRuntime::itemForeignKey();

        if ($replace) {
            $itemModelClass::where($itemForeignKey, $delivery->id)->delete();
        }

        foreach ($payload['item_id'] as $key => $itemId) {
            $itemModelClass::create([
                $itemForeignKey => $delivery->id,
                'purchase_item_id' => $itemId,
                'product_id' => $payload['product_id'][$key] ?? null,
                'quantity_ordered' => $payload['quantity_ordered'][$key] ?? 0,
                'quantity_received' => $payload['quantity_received'][$key] ?? 0,
                'batch_number' => $payload['batch_number'][$key] ?? null,
                'expiry_date' => $payload['expiry_date'][$key] ?? null,
                'picking_rule_applied' => $payload['picking_rule_applied'][$key] ?? null,
            ]);
        }
    }
}
