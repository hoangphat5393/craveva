<?php

namespace Modules\Purchase\Http\Requests\Product;

use App\Enums\ProductType;
use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;
use Illuminate\Validation\Rule;
use Modules\Purchase\Http\Requests\Product\Concerns\ResolvesProductSku;
use Modules\Purchase\Http\Requests\Product\Concerns\ValidatesProductUnitConversions;
use Modules\Purchase\Http\Requests\Product\Concerns\ValidatesPurchaseProductUnitType;

class StorePurchaseProductRequest extends CoreRequest
{
    use CustomFieldsRequestTrait;
    use ResolvesProductSku;
    use ValidatesProductUnitConversions;
    use ValidatesPurchaseProductUnitType;

    protected function prepareForValidation(): void
    {
        $this->mergeResolvedSku();

        $type = (string) $this->input('type');

        if (ProductType::forcesPurchaseInformationOnPurchaseForm($type)) {
            $this->merge(['purchase_information' => 1]);
        }

        if (ProductType::hidesCostPriceOnPurchaseForm($type)) {
            $this->merge([
                'purchase_information' => null,
                'purchase_price' => null,
            ]);
        }

        $this->mergePurchaseProductUnitTypeForValidation();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $companyId = company()->id;

        $rules = [
            'name' => [
                'required',
                Rule::unique('products')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'sku' => $this->skuRulesForStore($companyId),
            'track_inventory' => 'sometimes',
            'type' => ['required', Rule::in(ProductType::values())],
            'selling_price' => [
                Rule::requiredIf(fn() => ! ProductType::hidesSellingPriceOnPurchaseForm((string) $this->input('type'))),
                'nullable',
                'numeric',
                'min:0',
            ],
            'purchase_information' => 'sometimes',
            'opening_stock' => 'required_if:track_inventory,1',
            'purchase_price' => [
                Rule::requiredIf(fn() => ProductType::forcesPurchaseInformationOnPurchaseForm((string) $this->input('type'))
                    || (string) $this->input('purchase_information') === '1'),
                'nullable',
                'numeric',
                'min:0',
            ],
            'shelf_life_days' => 'nullable|integer|min:0',
        ];

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
            'purchase_price.required_if' => __('purchase::messages.purchasePriceRequired'),
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
