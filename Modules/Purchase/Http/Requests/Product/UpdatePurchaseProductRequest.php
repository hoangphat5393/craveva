<?php

namespace Modules\Purchase\Http\Requests\Product;

use App\Enums\ProductType;
use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;
use Illuminate\Validation\Rule;
use Modules\Purchase\Http\Requests\Product\Concerns\ResolvesProductSku;
use Modules\Purchase\Http\Requests\Product\Concerns\ValidatesProductUnitConversions;
use Modules\Purchase\Http\Requests\Product\Concerns\ValidatesPurchaseProductPricing;
use Modules\Purchase\Http\Requests\Product\Concerns\ValidatesPurchaseProductUnitType;

class UpdatePurchaseProductRequest extends CoreRequest
{
    use CustomFieldsRequestTrait;
    use ResolvesProductSku;
    use ValidatesProductUnitConversions;
    use ValidatesPurchaseProductPricing;
    use ValidatesPurchaseProductUnitType;

    protected function prepareForValidation(): void
    {
        $this->mergeResolvedSku();

        $type = (string) $this->input('type');

        $this->mergePurchaseProductPricingForValidation();
        $this->mergePurchaseProductUnitTypeForValidation();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => 'required|unique:products,name,' . $this->route('purchase_product') . ',id,company_id,' . company()->id,
            'sku' => $this->skuRulesForUpdate((int) company()->id, (int) $this->route('purchase_product')),
            'track_inventory' => 'sometimes',
            'type' => ['required', Rule::in(ProductType::values())],
            'selling_price' => [
                Rule::requiredIf(fn() => ! ProductType::hidesSellingPriceOnPurchaseForm((string) $this->input('type'))),
                'nullable',
                'numeric',
                'min:0',
            ],
            'opening_stock' => 'required_if:track_inventory,1',
            'wholesale_price' => 'nullable|numeric',
            'price_per_box' => 'nullable|numeric',
            'employee_price' => 'nullable|numeric',
            'inventory_type' => 'nullable|string',
            'shelf_life_days' => 'nullable|integer|min:0',

        ];

        $rules = array_merge($rules, $this->purchaseProductPricingRules());
        $rules = array_merge($rules, $this->purchaseProductUnitTypeRules());
        $rules = array_merge($rules, $this->productUnitConversionRulesForRequestType());

        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    public function messages()
    {
        return [
            'opening_stock.required_if' => __('purchase::messages.openingStockRequired'),
            'rate_per_unit.required_if' => __('purchase::messages.ratePerUnitRequired'),
            'selling_price.required_if' => __('purchase::messages.sellingPriceRequired'),
            'purchase_price.required' => __('purchase::messages.purchasePriceRequired'),
            'purchase_price.required_if' => __('purchase::messages.purchasePriceRequired'),
            'purchase_price.min' => __('purchase::messages.costPriceMinRequired'),
            'unit_type.required' => __('validation.required', ['attribute' => __('modules.unitType.unitType')]),
        ];
    }

    public function attributes()
    {
        $attributes = [];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
