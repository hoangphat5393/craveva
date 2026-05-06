<?php

namespace Modules\Production\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Production\Support\ProductionTenantAccess;

class StoreProductionReworkOrderRequest extends FormRequest
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
            'requested_quantity' => ['required', 'numeric', 'min:0.0001'],
            'reason' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
