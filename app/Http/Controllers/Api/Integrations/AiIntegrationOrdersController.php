<?php

namespace App\Http\Controllers\Api\Integrations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Integrations\StoreAiOrderWebhookRequest;
use App\Http\Requests\Integrations\UpdateAiIntegrationOrderRequest;
use App\Models\Company;
use App\Models\Order;
use App\Services\Integrations\AiOrderIntegrationAuthService;
use App\Services\Integrations\AiOrderWebhookOrderCreationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Warehouse\Exceptions\WarehouseBusinessException;

class AiIntegrationOrdersController extends Controller
{
    public function __construct(
        private readonly AiOrderWebhookOrderCreationService $orderCreationService
    ) {}

    public function store(StoreAiOrderWebhookRequest $request): JsonResponse
    {
        $company = $this->integrationCompany($request);

        $payload = $request->validated();
        if ((int) $payload['company_id'] !== (int) $company->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'company_id must match the company for this integration secret.',
            ], 422);
        }

        $externalEventId = $payload['external_event_id'] ?? null;
        if (! empty($externalEventId) && $this->orderCreationService->isDuplicateExternalEvent((int) $company->id, (string) $externalEventId)) {
            return response()->json([
                'status' => 'success',
                'message' => 'Event already processed.',
                'duplicate' => true,
            ]);
        }

        $items = $payload['items'];
        if (is_array($request->input('items'))) {
            $items = $request->input('items');
        }

        $warehouseIds = isset($payload['warehouse_ids']) && is_array($payload['warehouse_ids'])
            ? array_values(array_filter(array_map('intval', $payload['warehouse_ids'])))
            : [];

        $skipPayloadStockCheck = array_key_exists('check_stock', $payload) && $payload['check_stock'] === false;

        try {
            $this->orderCreationService->assertStockAllowsOrder((int) $company->id, $items, $warehouseIds, $skipPayloadStockCheck);
        } catch (WarehouseBusinessException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }

        $order = $this->orderCreationService->createOrder($company, $payload, $items);

        return response()->json([
            'status' => 'success',
            'message' => 'Order created from AI integration API.',
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'company_id' => $order->company_id,
                'total' => $order->total,
            ],
        ], 201);
    }

    public function show(Request $request, int $orderId): JsonResponse
    {
        $company = $this->integrationCompany($request);
        $order = Order::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->find($orderId);

        if ($order === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $this->orderSummary($order),
        ]);
    }

    public function update(UpdateAiIntegrationOrderRequest $request, int $orderId): JsonResponse
    {
        $company = $this->integrationCompany($request);
        $order = Order::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->find($orderId);

        if ($order === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found.',
            ], 404);
        }

        $payload = $request->validated();
        $audit = ' [ai_integration:patch:' . now()->toIso8601String() . ']';

        if (array_key_exists('status', $payload) && $payload['status'] !== null) {
            $order->status = (string) $payload['status'];
        }

        if (array_key_exists('note', $payload) && $payload['note'] !== null) {
            $order->note = trim((string) $order->note . ' ' . (string) $payload['note']) . $audit;
        } else {
            $order->note = trim((string) $order->note) . $audit;
        }

        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Order updated.',
            'data' => $this->orderSummary($order->fresh()),
        ]);
    }

    public function destroy(Request $request, int $orderId): JsonResponse
    {
        $company = $this->integrationCompany($request);
        $order = Order::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->find($orderId);

        if ($order === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found.',
            ], 404);
        }

        if (in_array($order->status, ['canceled', 'refunded'], true)) {
            return response()->json([
                'status' => 'success',
                'message' => 'Order already canceled or refunded.',
                'data' => $this->orderSummary($order),
            ]);
        }

        $order->status = 'canceled';
        $order->note = trim((string) $order->note . ' [ai_integration:delete:' . now()->toIso8601String() . ']');
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Order canceled via integration API.',
            'data' => $this->orderSummary($order->fresh()),
        ]);
    }

    private function integrationCompany(Request $request): Company
    {
        $company = $request->attributes->get(AiOrderIntegrationAuthService::REQUEST_ATTRIBUTE_COMPANY);
        if (! $company instanceof Company) {
            abort(401);
        }

        return $company;
    }

    /**
     * @return array<string, mixed>
     */
    private function orderSummary(Order $order): array
    {
        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'company_id' => $order->company_id,
            'status' => $order->status,
            'total' => $order->total,
            'note' => $order->note,
        ];
    }
}
