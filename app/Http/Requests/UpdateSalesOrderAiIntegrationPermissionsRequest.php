<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesOrderAiIntegrationPermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'ai_order_integration_allow_create' => ['required', 'boolean'],
            'ai_order_integration_allow_read' => ['required', 'boolean'],
            'ai_order_integration_allow_update' => ['required', 'boolean'],
            'ai_order_integration_allow_delete' => ['required', 'boolean'],
        ];
    }
}
