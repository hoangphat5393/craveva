<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Integrations\StoreAiOrderWebhookRequest;
use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\Order;
use App\Models\OrderItems;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AiOrderWebhookController extends Controller
{
    public function store(StoreAiOrderWebhookRequest $request, string $hash): JsonResponse
    {
        $expectedSecret = (string) config('app.ai_order_webhook_secret', env('AI_ORDER_WEBHOOK_SECRET', ''));
        $headerSecret = (string) $request->header('X-AI-Webhook-Secret', '');

        if ($expectedSecret === '' || ! hash_equals($expectedSecret, $hash) || ! hash_equals($expectedSecret, $headerSecret)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized webhook request.',
            ], 401);
        }

        $payload = $request->validated();
        $companyId = (int) $payload['company_id'];
        $externalEventId = $payload['external_event_id'] ?? null;

        // Basic idempotency for pilot: skip duplicate external event IDs.
        if (! empty($externalEventId)) {
            $exists = Order::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('note', 'like', '%[ai_event:' . $externalEventId . ']%')
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Event already processed.',
                    'duplicate' => true,
                ]);
            }
        }

        $company = Company::withoutGlobalScopes()->findOrFail($companyId);
        $items = $payload['items'];

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

        $metaTag = ! empty($externalEventId) ? '[ai_event:' . $externalEventId . ']' : '[ai_event:manual-test]';
        $note = trim(($payload['note'] ?? '') . ' ' . $metaTag);

        $order = DB::transaction(function () use ($payload, $company, $defaultAddressId, $subTotal, $total, $discountValue, $discountType, $items, $note) {
            $order = new Order();
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

        return response()->json([
            'status' => 'success',
            'message' => 'Order created from AI webhook.',
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'company_id' => $order->company_id,
                'total' => $order->total,
            ],
        ], 201);
    }
}
