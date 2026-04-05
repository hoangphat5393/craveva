<?php

namespace App\Http\Requests;

use App\Traits\CustomFieldsRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEstimate extends FormRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->estimate_number && is_numeric($this->estimate_number)) {
            $this->merge([
                'estimate_number' => \App\Helper\NumberFormat::estimate($this->estimate_number),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'estimate_number' => [
                'required',
                /** @phpstan-ignore-next-line */
                Rule::unique('estimates')->where('company_id', company()->id)
                    ->when($this->route('estimate'), function ($q) {
                        /** @phpstan-ignore-next-line */
                        $q->where('id', '<>', $this->route('estimate'));
                    }),
            ],
            'client_id' => 'required',
            'valid_till' => 'required',
            'sub_total' => 'required',
            'total' => 'required',
            'currency_id' => 'required',
            'quotation_date' => 'nullable|string',
            'document_date' => 'nullable|string',
            'exchange_rate' => 'nullable|string',
            'header_quotation_amount' => 'nullable|string',
            'header_tax_amount' => 'nullable|string',
            'header_total_quantity' => 'nullable|string',
            'delivery_note' => 'nullable|string',
            'salesperson_name' => 'nullable|string|max:191',
            'tax_type_label' => 'nullable|string|max:191',
            'payment_terms_code' => 'nullable|string|max:64',
            'payment_terms_name' => 'nullable|string|max:255',
            'confirm_internal' => 'nullable|string|max:16',
            'confirm_customer' => 'nullable|string|max:16',
            'price_terms' => 'nullable|string|max:255',
            'volume_unit' => 'nullable|string|max:64',
            'total_gross_weight_kg' => 'nullable|string',
            'total_volume' => 'nullable|string',
            'item_free_quantity' => 'nullable|array',
            'item_free_quantity.*' => 'nullable|string',
            'item_line_effective_date' => 'nullable|array',
            'item_line_effective_date.*' => 'nullable|string',
            'item_line_expiry_date' => 'nullable|array',
            'item_line_expiry_date.*' => 'nullable|string',
        ];

        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    public function attributes()
    {
        $attributes = [];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

    public function messages()
    {
        return [
            'client_id.required' => __('modules.projects.selectClient'),
        ];
    }
}
