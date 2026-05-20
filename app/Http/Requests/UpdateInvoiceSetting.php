<?php

namespace App\Http\Requests;

class UpdateInvoiceSetting extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'due_after' => 'required|numeric',
            'invoice_terms' => 'required',
            'phase1_min_gross_margin_percent' => 'nullable|numeric|min:0|max:100',
        ];

        return $rules;
    }
}
