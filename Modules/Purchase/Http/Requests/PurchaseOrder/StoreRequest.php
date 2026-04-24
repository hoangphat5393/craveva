<?php

namespace Modules\Purchase\Http\Requests\PurchaseOrder;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $setting = company();

        $rules = [
            'purchase_order_number' => [
                'required',
                Rule::unique('purchase_orders')->where('company_id', company()->id),
            ],
            'vendor_id' => 'required',
            'purchase_date' => 'required|date_format:"' . $setting->date_format . '"|before_or_equal:expected_date',
            'expected_date' => 'required|date_format:"' . $setting->date_format . '"|after_or_equal:purchase_date',
            'exchange_rate' => 'required',
            'warehouse_id' => [
                'nullable',
                'integer',
                Rule::exists('warehouses', 'id')->where('company_id', company()->id),
            ],
        ];

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

    public function messages(): array
    {
        return [
            'vendor_id.required' => __('validation.required', ['attribute' => __('purchase::app.menu.vendor')]),
            'purchase_order_number.required' => __('validation.required', ['attribute' => __('purchase::modules.order.orderNumber')]),
            'purchase_date.required' => __('validation.required', ['attribute' => __('purchase::modules.order.purchaseDate')]),
            'expected_date.required' => __('validation.required', ['attribute' => __('purchase::modules.order.expectedDate')]),
            'exchange_rate.required' => __('validation.required', ['attribute' => __('purchase::modules.order.exchangeRate')]),
            'warehouse_id.exists' => __('validation.exists', ['attribute' => __('purchase::app.warehouse')]),
        ];
    }

    public function attributes(): array
    {
        return [
            'vendor_id' => __('purchase::app.menu.vendor'),
            'purchase_order_number' => __('purchase::modules.order.orderNumber'),
            'purchase_date' => __('purchase::modules.order.purchaseDate'),
            'expected_date' => __('purchase::modules.order.expectedDate'),
            'exchange_rate' => __('purchase::modules.order.exchangeRate'),
            'warehouse_id' => __('purchase::app.warehouse'),
        ];
    }
}
