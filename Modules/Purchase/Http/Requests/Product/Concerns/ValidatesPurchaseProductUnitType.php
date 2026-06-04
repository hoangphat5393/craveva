<?php

declare(strict_types=1);

namespace Modules\Purchase\Http\Requests\Product\Concerns;

use App\Enums\ProductType;
use Illuminate\Validation\Rule;

trait ValidatesPurchaseProductUnitType
{
    protected function mergePurchaseProductUnitTypeForValidation(): void
    {
        if (ProductType::isService((string) $this->input('type'))) {
            $this->merge(['unit_type' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function purchaseProductUnitTypeRules(): array
    {
        $companyId = (int) company()->id;

        return [
            'unit_type' => [
                Rule::requiredIf(fn() => ! ProductType::isService((string) $this->input('type'))),
                'nullable',
                'integer',
                Rule::exists('unit_types', 'id')->where(fn($query) => $query->where('company_id', $companyId)),
            ],
        ];
    }
}
