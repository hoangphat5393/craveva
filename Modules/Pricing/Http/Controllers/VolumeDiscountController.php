<?php

namespace Modules\Pricing\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Pricing\Services\VolumeDiscountService;

class VolumeDiscountController extends AccountBaseController
{
    public function calculate(Request $request, VolumeDiscountService $service): JsonResponse
    {
        $items = $request->input('items', []);

        if (! is_array($items)) {
            $items = [];
        }

        $result = $service->calculate($items);

        return response()->json([
            'status' => 'success',
            'global_discount' => [
                'value' => $result['value'] ?? 0,
            ],
        ]);
    }
}
