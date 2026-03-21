<?php

namespace Modules\Purchase\Http\Requests\PurchaseOrder;

use Illuminate\Foundation\Http\FormRequest;

class ChangeStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'delivery_status' => 'required|in:delivered,delivery_failed,in_transaction,not_started',
        ];
    }
}
