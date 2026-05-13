<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Integrations\StoreAiOrderWebhookRequest;
use App\Models\Company;
use App\Services\Integrations\AiOrderWebhookOrderCreationService;
use Illuminate\Http\JsonResponse;
use Modules\Warehouse\Exceptions\WarehouseBusinessException;

class AiOrderWebhookController extends Controller
{
    public function __construct(
        private readonly AiOrderWebhookOrderCreationService $orderCreationService
    ) {}

    public function store(StoreAiOrderWebhookRequest $request, string $hash): JsonResponse
    {
        $headerSecret = (string) $request->header('X-AI-Webhook-Secret', '');

        $companyForSecret = Company::withoutGlobalScopes()
            ->whereNotNull('ai_order_webhook_secret')
            ->where('ai_order_webhook_secret', $hash)
            ->first();

        $globalSecret = (string) config('app.ai_order_webhook_secret', '');

        $authorized = false;

        if ($companyForSecret !== null && hash_equals((string) $companyForSecret->ai_order_webhook_secret, $headerSecret)) {
            $authorized = true;
        } elseif ($globalSecret !== '' && hash_equals($globalSecret, $hash) && hash_equals($globalSecret, $headerSecret)) {
            $authorized = true;
            $companyForSecret = null;
        }

        if (! $authorized) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized webhook request.',
            ], 401);
        }

        if ($companyForSecret !== null && ! $companyForSecret->ai_order_integration_allow_create) {
            return response()->json([
                'status' => 'error',
                'code' => 'INTEGRATION_METHOD_DISABLED',
                'message' => 'This HTTP method is disabled for AI order integration in company settings.',
            ], 403);
        }

        $payload = $request->validated();
        $resolvedClientId = $request->input('client_id');
        $payload['client_id'] = is_numeric($resolvedClientId) ? (int) $resolvedClientId : null;

        $mergedItems = $request->input('items');
        if (is_array($mergedItems)) {
            $payload['items'] = $mergedItems;
        }

        if ($companyForSecret !== null && (int) $payload['company_id'] !== (int) $companyForSecret->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'company_id must match the company for this webhook secret.',
            ], 422);
        }

        $companyId = (int) $payload['company_id'];

        if ($companyForSecret === null) {
            $companyForCreateCheck = Company::withoutGlobalScopes()->find($companyId);
            if ($companyForCreateCheck === null || ! $companyForCreateCheck->ai_order_integration_allow_create) {
                return response()->json([
                    'status' => 'error',
                    'code' => 'INTEGRATION_METHOD_DISABLED',
                    'message' => 'This HTTP method is disabled for AI order integration in company settings.',
                ], 403);
            }
        }

        $externalEventId = $payload['external_event_id'] ?? null;

        if (! empty($externalEventId) && $this->orderCreationService->isDuplicateExternalEvent($companyId, (string) $externalEventId)) {
            return response()->json([
                'status' => 'success',
                'message' => 'Event already processed.',
                'duplicate' => true,
            ]);
        }

        $company = Company::withoutGlobalScopes()->findOrFail($companyId);
        $items = $payload['items'];

        $warehouseIds = isset($payload['warehouse_ids']) && is_array($payload['warehouse_ids'])
            ? array_values(array_filter(array_map('intval', $payload['warehouse_ids'])))
            : [];

        $skipPayloadStockCheck = array_key_exists('check_stock', $payload) && $payload['check_stock'] === false;

        try {
            $this->orderCreationService->assertStockAllowsOrder($companyId, $items, $warehouseIds, $skipPayloadStockCheck);
        } catch (WarehouseBusinessException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }

        $order = $this->orderCreationService->createOrder($company, $payload, $items);

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
