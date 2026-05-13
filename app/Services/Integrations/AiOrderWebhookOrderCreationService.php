<?php

namespace App\Services\Integrations;

use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\Order;
use App\Models\OrderItems;
use Illuminate\Support\Facades\DB;
use Modules\Warehouse\Exceptions\WarehouseBusinessException;
use Modules\Warehouse\Services\WarehouseAvailabilityService;
use Modules\Warehouse\Services\WarehouseFlowConfigService;

final class AiOrderWebhookOrderCreationService
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, array<string, mixed>>  $items
     */
    public function createOrder(Company $company, array $payload, array $items): Order
    {
        $companyId = (int) $company->id;

        $subTotal = collect($items)->sum(function ($item) {
            return round((float) $item['quantity'] * (float) $item['unit_price'], 2);
        });

        $discountType = $payload['discount_type'] ?? 'fixed';
        $discountValue = round((float) ($payload['discount_value'] ?? 0), 2);
        $discountAmount = $discountType === 'percent'
            ? round($subTotal * ($discountValue / 100), 2)
            : $discountValue;
        $total = max(0, round($subTotal - $discountAmount, 2));

        $defaultAddressId = $payload['company_address_id'] ?? CompanyAddress::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('is_default', 1)
            ->value('id');

        if (empty($defaultAddressId)) {
            $defaultAddressId = CompanyAddress::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->value('id');
        }

        $externalEventId = $payload['external_event_id'] ?? null;
        $metaTag = ! empty($externalEventId) ? '[ai_event:'.$externalEventId.']' : '[ai_event:manual-test]';
        $note = trim(($payload['note'] ?? '').' '.$metaTag);

        return DB::transaction(function () use ($payload, $company, $defaultAddressId, $subTotal, $total, $discountValue, $discountType, $items, $note) {
            $order = new Order;
            $order->company_id = $company->id;
            $order->client_id = $payload['client_id'] ?? null;
            $order->project_id = $payload['project_id'] ?? null;
            $order->order_date = now()->format('Y-m-d');
            $order->sub_total = $subTotal;
            $order->total = $total;
            $order->discount = $discountValue;
            $order->discount_type = $discountType;
            $order->status = $payload['status'] ?? 'pending';
            $order->currency_id = $company->currency_id;
            $order->note = $note;
            $order->show_shipping_address = 'no';
            $order->company_address_id = $defaultAddressId;
            $order->save();

            foreach ($items as $index => $item) {
                $quantity = round((float) $item['quantity'], 2);
                $unitPrice = round((float) $item['unit_price'], 2);

                OrderItems::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'item_summary' => $item['item_summary'] ?? null,
                    'type' => 'item',
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'amount' => round($quantity * $unitPrice, 2),
                    'taxes' => ! empty($item['taxes']) ? json_encode($item['taxes']) : null,
                    'sku' => $item['sku'] ?? null,
                    'field_order' => $index + 1,
                ]);
            }

            return $order->fresh();
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     *
     * @throws WarehouseBusinessException
     */
    public function assertStockAllowsOrder(int $companyId, array $items, array $warehouseIds, bool $skipPayloadStockCheck): void
    {
        if (app(WarehouseFlowConfigService::class)->aiOrderWebhookCheckStock($companyId) && ! $skipPayloadStockCheck) {
            app(WarehouseAvailabilityService::class)->validateAiOrderWebhookItems($companyId, $items, $warehouseIds);
        }
    }

    public function isDuplicateExternalEvent(int $companyId, string $externalEventId): bool
    {
        return Order::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('note', 'like', '%[ai_event:'.$externalEventId.']%')
            ->exists();
    }
}
