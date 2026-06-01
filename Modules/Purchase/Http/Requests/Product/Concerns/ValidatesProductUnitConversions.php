<?php

declare(strict_types=1);

namespace Modules\Purchase\Http\Requests\Product\Concerns;

use App\Enums\ProductType;

trait ValidatesProductUnitConversions
{
    /**
     * @return array<string, mixed>
     */
    protected function productUnitConversionRulesForRequestType(): array
    {
        $type = (string) ($this->input('type') ?? '');

        if (! ProductType::supportsAlternateUnitConversions($type)) {
            return [];
        }

        return $this->productUnitConversionRules();
    }

    /**
     * @return array<string, mixed>
     */
    protected function productUnitConversionRules(): array
    {
        return [
            'unit_conversion_unit_id' => 'sometimes|array',
            'unit_conversion_unit_id.*' => 'nullable|integer|exists:unit_types,id',
            'unit_conversion_factor' => 'sometimes|array',
            'unit_conversion_factor.*' => 'nullable|numeric|gt:0',
            'unit_conversion_selling_price' => 'sometimes|array',
            'unit_conversion_selling_price.*' => 'nullable|numeric|min:0',
            'unit_conversion_for_sale' => 'sometimes|array',
            'unit_conversion_for_sale.*' => 'nullable|in:0,1',
        ];
    }
}
