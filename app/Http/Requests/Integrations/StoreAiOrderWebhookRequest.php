<?php

namespace App\Http\Requests\Integrations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAiOrderWebhookRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! empty($this->all())) {
            return;
        }

        $decoded = json_decode((string) $this->getContent(), true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $this->merge($decoded);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'client_id' => ['required', 'integer', 'exists:users,id'],
            'external_event_id' => ['nullable', 'string', 'max:191'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'company_address_id' => ['nullable', 'integer', 'exists:company_addresses,id'],
            'status' => ['nullable', Rule::in(['pending', 'on-hold', 'failed', 'processing', 'completed', 'canceled', 'refunded'])],
            'discount_type' => ['nullable', Rule::in(['percent', 'fixed'])],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.item_summary' => ['nullable', 'string'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.sku' => ['nullable', 'string', 'max:191'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.taxes' => ['nullable', 'array'],
        ];
    }
}
