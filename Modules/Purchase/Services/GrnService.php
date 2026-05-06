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
            $requestedStatus = (string) ($payload['status'] ?? 'draft');
            $initialPayload = $payload;

            // When GRN is created directly as "received", the header "saved" observer
            // can run before item rows exist and skip inbound posting.
            // Persist as inbound first, sync items, then finalize to received.
            if ($requestedStatus === 'received') {
                $initialPayload['status'] = 'inbound';
            }

            $headerModelClass = GrnRuntime::headerModelClass();
            $delivery = new $headerModelClass;
            $delivery->company_id = $companyId;
            $this->fillDelivery($delivery, $initialPayload);
            $delivery->save();

            $this->syncItems($delivery, $payload);

            if ($delivery->status !== $requestedStatus) {
                $delivery->status = $requestedStatus;
                $delivery->save();
            }

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
        $hasQcColumns = DB::getSchemaBuilder()->hasColumn(GrnRuntime::itemTable(), 'qc_status');

        if ($replace) {
            $itemModelClass::where($itemForeignKey, $delivery->id)->delete();
        }

        foreach ($payload['item_id'] as $key => $itemId) {
            $itemData = [
                $itemForeignKey => $delivery->id,
                'purchase_item_id' => $itemId,
                'product_id' => $payload['product_id'][$key] ?? null,
                'quantity_ordered' => $payload['quantity_ordered'][$key] ?? 0,
                'quantity_received' => $payload['quantity_received'][$key] ?? 0,
                'batch_number' => $payload['batch_number'][$key] ?? null,
                'expiry_date' => $payload['expiry_date'][$key] ?? null,
                'picking_rule_applied' => $payload['picking_rule_applied'][$key] ?? null,
            ];

            if ($hasQcColumns) {
                $itemData['qc_status'] = $this->normalizeQcStatus($payload['qc_status'][$key] ?? null);
                $itemData['qc_reviewed_by'] = $payload['qc_reviewed_by'][$key] ?? null;
                $itemData['qc_reviewed_at'] = $payload['qc_reviewed_at'][$key] ?? null;
            }

            $itemModelClass::create($itemData);
        }
    }

    protected function normalizeQcStatus(mixed $value): string
    {
        $status = strtolower(trim((string) ($value ?? '')));

        if (! in_array($status, ['pending', 'accepted', 'rejected'], true)) {
            return 'accepted';
        }

        return $status;
    }
}
