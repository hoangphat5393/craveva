<?php

namespace Modules\Purchase\Http\Requests\Inventory;

use App\Http\Requests\CoreRequest;

class StorePurchaseInventoryRequest extends CoreRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'date' => 'required|date',
            'reason_id' => 'required|numeric',
            'warehouse_id' => 'nullable|numeric',
            'type' => 'required|in:quantity,value',
            'quantity_adjusted*' => 'required_if:type,quantity',
            'adjusted_value*' => 'required_if:type,value',
            'manufacturing_date.*' => 'nullable|date',
            'expiration_date.*' => 'nullable|date',
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'reason_id.required' => __('purchase::messages.inventory.reason'),
            'type.required' => __('purchase::messages.inventory.type'),
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('manufacturing_date')) {
            $manufacturingDates = $this->manufacturing_date;
            foreach ($manufacturingDates as $key => $date) {
                if ($date) {
                    try {
                        $manufacturingDates[$key] = \Carbon\Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
                    } catch (\Exception $e) {
                    }
                }
            }
            $this->merge(['manufacturing_date' => $manufacturingDates]);
        }

        if ($this->has('expiration_date')) {
            $expirationDates = $this->expiration_date;
            foreach ($expirationDates as $key => $date) {
                if ($date) {
                    try {
                        $expirationDates[$key] = \Carbon\Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
                    } catch (\Exception $e) {
                    }
                }
            }
            $this->merge(['expiration_date' => $expirationDates]);
        }
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
