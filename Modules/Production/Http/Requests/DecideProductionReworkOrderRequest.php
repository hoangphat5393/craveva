<?php

namespace Modules\Production\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Production\Support\ProductionTenantAccess;

class DecideProductionReworkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ProductionTenantAccess::tenantMayUseProduction()
            && in_array(user()->permission('edit_production_orders'), ['all', 'added', 'owned', 'both'], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'approved_quantity' => ['nullable', 'numeric', 'min:0.0001'],
            'decision_note' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
