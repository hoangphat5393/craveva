<?php

declare(strict_types=1);

namespace Modules\Purchase\Http\Requests\Product\Concerns;

use App\Enums\ProductType;
use Illuminate\Validation\Rule;

trait ValidatesPurchaseProductPricing
{
    protected function mergePurchaseProductPricingForValidation(): void
    {
        $type = (string) $this->input('type');

        if (! ProductType::supportsCostFromBomOnPurchaseForm($type)) {
            $this->merge(['cost_from_bom' => false]);
        }

        if (ProductType::hidesCostPriceOnPurchaseForm($type)) {
            $this->merge([
                'purchase_price' => null,
                'cost_from_bom' => false,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function purchaseProductPricingRules(): array
    {
        $type = (string) $this->input('type');
        $costFromBom = $this->boolean('cost_from_bom');

        return [
            'cost_from_bom' => ['sometimes', 'boolean'],
            'purchase_price' => [
                Rule::requiredIf(
                    fn() => ProductType::requiresPurchasePriceOnPurchaseForm($type, $costFromBom)
                ),
                'nullable',
                'numeric',
                Rule::when(
                    ProductType::requiresPurchasePriceOnPurchaseForm($type, $costFromBom),
                    ['min:0.01'],
                    ['min:0'],
                ),
            ],
        ];
    }
}
