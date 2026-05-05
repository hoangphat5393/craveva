<?php

namespace Modules\Production\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Support\ProductionTenantAccess;

class StoreProductionOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ProductionTenantAccess::tenantMayUseProduction()
            && in_array(user()->permission('add_production_orders'), ['all', 'added', 'owned', 'both'], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $companyId = (int) (company()?->id ?? 0);

        return [
            'output_product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('company_id', $companyId)],
            'production_bom_id' => [
                'nullable',
                'integer',
                Rule::exists('production_boms', 'id')->where(function ($query) use ($companyId): void {
                    $query->whereNull('company_id')->orWhere('company_id', $companyId);
                }),
            ],
            'rm_warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')->where('company_id', $companyId)],
            'fg_warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')->where('company_id', $companyId)],
            'planned_quantity' => ['required', 'numeric', 'min:0.0001'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $bomId = $this->input('production_bom_id');
            if ($bomId === null || $bomId === '') {
                return;
            }

            $bom = ProductionBom::query()->find((int) $bomId);
            if ($bom === null) {
                return;
            }

            if ((int) $bom->output_product_id !== (int) $this->input('output_product_id')) {
                $validator->errors()->add('production_bom_id', __('production::app.bomOutputMismatch'));
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'output_product_id.required' => __('validation.required', ['attribute' => __('production::app.fgProduct')]),
            'planned_quantity.min' => __('validation.min.numeric', ['attribute' => __('production::app.plannedQty'), 'min' => '0.0001']),
        ];
    }
}
