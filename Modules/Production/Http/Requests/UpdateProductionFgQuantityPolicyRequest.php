<?php

namespace Modules\Production\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Production\Services\ProductionFgQuantityPolicyService;
use Modules\Production\Support\ProductionTenantAccess;

class UpdateProductionFgQuantityPolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return user()->permission('manage_company_setting') === 'all'
            && ProductionTenantAccess::tenantMayUseProduction();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'policy_mode' => [
                'required',
                'string',
                Rule::in([
                    ProductionFgQuantityPolicyService::MODE_STRICT,
                    ProductionFgQuantityPolicyService::MODE_CONTROLLED,
                    ProductionFgQuantityPolicyService::MODE_FLEXIBLE,
                ]),
            ],
            'tolerance_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'tolerance_absolute' => ['required', 'numeric', 'min:0'],
            'controlled_require_reason_beyond_tolerance' => ['required', 'boolean'],
            'controlled_block_beyond_tolerance' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'controlled_require_reason_beyond_tolerance' => $this->boolean('controlled_require_reason_beyond_tolerance'),
            'controlled_block_beyond_tolerance' => $this->boolean('controlled_block_beyond_tolerance'),
        ]);
    }
}
