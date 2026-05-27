<?php

namespace Modules\Production\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Http\Requests\Concerns\ValidatesProductionOrderBomPolicy;
use Modules\Production\Support\ProductionTenantAccess;

class StoreProductionOrderRequest extends FormRequest
{
    use ValidatesProductionOrderBomPolicy;

    protected function prepareForValidation(): void
    {
        $bomId = $this->input('production_bom_id');
        if ($bomId === null || $bomId === '') {
            return;
        }

        $bom = ProductionBom::query()->find((int) $bomId);
        if ($bom === null) {
            return;
        }

        $this->merge([
            'output_product_id' => (int) $bom->output_product_id,
        ]);
    }

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
            'production_bom_id' => $this->productionBomIdRules($companyId),
            'rm_warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')->where('company_id', $companyId)],
            'fg_warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')->where('company_id', $companyId)],
            'planned_quantity' => ['required', 'numeric', 'min:0.0001'],
            'sales_order_id' => [
                'nullable',
                'integer',
                Rule::exists('orders', 'id')->where(function ($query) use ($companyId): void {
                    $query->where('company_id', $companyId)
                        ->whereNotIn('status', Order::STATUSES_CLOSED_FOR_PRODUCTION_ORDER_LINK);
                }),
            ],
            'project_id' => [
                'nullable',
                'integer',
                Rule::exists('projects', 'id')->where('company_id', $companyId),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $this->validateProductionOrderBomPolicy($validator);

            $bomId = $this->input('production_bom_id');
            if ($bomId === null || $bomId === '') {
                return;
            }

            $bom = ProductionBom::query()->find((int) $bomId);
            if ($bom === null) {
                return;
            }

            if ((int) $bom->output_product_id !== (int) $this->input('output_product_id')) {
                $validator->errors()->add('production_bom_id', __('production::app.bomOutputManufacturedProductMismatch'));
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'output_product_id.required' => __('validation.required', ['attribute' => __('production::app.manufacturedProduct')]),
            'planned_quantity.min' => __('validation.min.numeric', ['attribute' => __('production::app.plannedQty'), 'min' => '0.0001']),
            'sales_order_id.exists' => __('production::app.salesOrderMustBeOpen'),
        ];
    }
}
