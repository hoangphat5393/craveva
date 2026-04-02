<?php

namespace Modules\Warehouse\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Warehouse\Services\WarehouseAvailabilityService;

class WarehouseAvailabilityController
{
    public function __construct(
        protected WarehouseAvailabilityService $availabilityService
    ) {}

    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|integer|min:1',
            'product_id' => 'required|integer|min:1',
            'warehouse_ids' => 'nullable|array',
            'warehouse_ids.*' => 'integer|min:1',
        ]);

        $result = $this->availabilityService->availabilityByProduct(
            (int) $validated['company_id'],
            (int) $validated['product_id'],
            array_values(array_map('intval', $validated['warehouse_ids'] ?? []))
        );

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }
}
