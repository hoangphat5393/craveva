<?php

namespace Modules\Production\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Entities\ProductionOrder;
use Modules\Production\Support\ProductionTenantAccess;

class UpdateProductionOrderRequest extends FormRequest
{
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
            && in_array(user()->permission('edit_production_orders'), ['all', 'added', 'owned', 'both'], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $companyId = (int) (company()?->id ?? 0);
        /** @var ProductionOrder|null $productionOrder */
        $productionOrder = $this->route('order');
        $retainableSalesOrderId = $productionOrder instanceof ProductionOrder
            ? $productionOrder->sales_order_id
            : null;

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
            'sales_order_id' => [
                'nullable',
                'integer',
                Rule::exists('orders', 'id')->where(function ($query) use ($companyId, $retainableSalesOrderId): void {
                    $query->where('company_id', $companyId)
                        ->where(function ($inner) use ($retainableSalesOrderId): void {
                            $inner->whereNotIn('status', Order::STATUSES_CLOSED_FOR_PRODUCTION_ORDER_LINK);
                            if ($retainableSalesOrderId !== null) {
                                $inner->orWhere('id', (int) $retainableSalesOrderId);
                            }
                        });
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
            /** @var ProductionOrder|null $order */
            $order = $this->route('order');
            if ($order instanceof ProductionOrder && $order->status !== ProductionOrder::STATUS_DRAFT) {
                $validator->errors()->add('status', __('production::app.onlyDraftEditable'));
            }

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
            'sales_order_id.exists' => __('production::app.salesOrderMustBeOpen'),
        ];
    }
}
