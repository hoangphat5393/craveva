<?php

namespace Modules\Pricing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Pricing\Services\PricingService;

class PricingController extends Controller
{
    public function preview(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        abort_403(! in_array('pricing', array_map('strtolower', $user->modules ?? [])));

        $validated = $request->validate([
            'product_id' => 'required|integer|min:1',
            'client_id' => 'nullable|integer|min:0',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $productId = (int) $validated['product_id'];
        $clientId = (int) ($validated['client_id'] ?? 0);
        $quantity = max(1, (int) ($validated['quantity'] ?? 1));

        $companyId = company()?->id ?? ($user->company_id ? (int) $user->company_id : null);
        if ($companyId) {
            $product = Product::query()
                ->where('id', $productId)
                ->where('company_id', $companyId)
                ->first();
            abort_if(! $product, 404);
        } else {
            Product::findOrFail($productId);
        }

        if ($clientId > 0 && $companyId) {
            $clientUser = User::withoutGlobalScopes()->find($clientId);
            abort_if(
                ! $clientUser || (int) $clientUser->company_id !== $companyId,
                403,
                'Client does not belong to this company.'
            );
        }

        $result = app(PricingService::class)->calculate($productId, $clientId, $quantity);

        return response()->json($result);
    }
}
