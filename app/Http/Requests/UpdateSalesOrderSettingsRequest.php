<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesOrderSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return user()->permission('manage_finance_setting') === 'all'
            && in_array('orders', user_modules());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'order_prefix' => 'required|string|max:50',
            'order_number_separator' => 'nullable|string|max:10',
            'order_digit' => 'nullable|integer|min:0|max:10',
            'order_terms' => 'nullable|string',
        ];
    }
}
