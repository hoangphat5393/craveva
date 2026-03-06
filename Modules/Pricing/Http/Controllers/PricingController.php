<?php

namespace Modules\Pricing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Pricing\Services\PricingService;

class PricingController extends Controller
{
    public function preview(Request $request)
    {
        $productId = (int) $request->get('product_id');
        $clientId = (int) $request->get('client_id');
        $quantity = (int) ($request->get('quantity') ?? 1);

        $service = new PricingService;
        $result = $service->calculate($productId, $clientId, $quantity);

        return response()->json($result);
    }
}
