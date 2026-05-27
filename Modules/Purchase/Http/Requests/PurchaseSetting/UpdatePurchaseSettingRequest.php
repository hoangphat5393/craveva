<?php

namespace Modules\Purchase\Http\Requests\PurchaseSetting;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class UpdatePurchaseSettingRequest extends CoreRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'purchase_order_prefix' => 'required',
            'bill_prefix' => 'required',
            'vendor_credit_prefix' => 'required',
            'purchase_terms' => 'nullable|string',
            'grn_terms' => 'nullable|string',
        ];

        $rules = $this->customFieldRules($rules);

        return $rules;
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
