<?php

namespace App\Http\Requests\Integrations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAiIntegrationOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(['pending', 'on-hold', 'failed', 'processing', 'completed', 'canceled', 'refunded'])],
            'note' => ['nullable', 'string', 'max:65535'],
        ];
    }
}
