<?php

namespace Modules\Warehouse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

class UpdateWarehouseCompanyFlowSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return user()->permission('manage_company_setting') === 'all'
            && in_array('warehouse', user_modules() ?? [], true);
    }

    /**
     * @return array<string, array<int, string|In>>
     */
    public function rules(): array
    {
        return [
            'allow_negative_stock' => ['required', 'boolean'],
            'strict_unit_conversion' => ['required', 'boolean'],
            'inbound_from_purchase_order_delivered' => ['required', 'boolean'],
            'inbound_from_delivery_order_received' => ['required', 'boolean'],
            'sales_outbound_enabled' => ['required', 'boolean'],
            'sales_outbound_mode' => ['required', Rule::in(['shipment', 'invoice'])],
            'ai_order_webhook_check_stock' => ['required', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->boolean('inbound_from_purchase_order_delivered') && $this->boolean('inbound_from_delivery_order_received')) {
                $validator->errors()->add(
                    'inbound_from_delivery_order_received',
                    __('warehouse::app.err_inbound_both_sources_true')
                );
            }
        });
    }
}
