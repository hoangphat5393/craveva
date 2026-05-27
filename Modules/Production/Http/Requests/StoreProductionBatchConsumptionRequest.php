<?php

namespace Modules\Production\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Production\Entities\ProductionBatch;
use Modules\Production\Entities\ProductionOrder;
use Modules\Production\Support\ProductionBomFirstPolicy;
use Modules\Production\Support\ProductionTenantAccess;
use Modules\Warehouse\Entities\WarehouseProductBatch;

class StoreProductionBatchConsumptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! ProductionBomFirstPolicy::allowManualBatchConsumptionLines()) {
            return false;
        }

        return ProductionTenantAccess::tenantMayUseProduction()
            && in_array(user()->permission('edit_production_orders'), ['all', 'added', 'owned', 'both'], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $companyId = (int) (company()?->id ?? 0);

        return [
            'component_product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('company_id', $companyId)],
            'warehouse_product_batch_id' => ['required', 'integer', 'exists:warehouse_product_batches,id'],
            'planned_quantity' => ['required', 'numeric', 'min:0.0001'],
        ];
    }

    public function withValidator($validator): void
    {
        $companyId = (int) (company()?->id ?? 0);

        $validator->after(function ($validator) use ($companyId): void {
            /** @var ProductionBatch|null $batch */
            $batch = $this->route('batch');
            if (! $batch instanceof ProductionBatch) {
                return;
            }

            $order = $batch->order;
            if (! $order instanceof ProductionOrder) {
                return;
            }

            if (! in_array($order->status, [ProductionOrder::STATUS_RELEASED, ProductionOrder::STATUS_IN_PROGRESS], true)) {
                $validator->errors()->add('status', __('production::app.consumptionRequiresReleased'));
            }

            if ($batch->posted_consumptions_at !== null) {
                $validator->errors()->add('batch', __('production::app.batchAlreadyConsumed'));
            }

            $wpbId = (int) $this->input('warehouse_product_batch_id', 0);
            if ($wpbId <= 0) {
                return;
            }

            $wpb = WarehouseProductBatch::query()->find($wpbId);
            if ($wpb === null) {
                return;
            }

            if ((int) $wpb->company_id !== $companyId) {
                $validator->errors()->add('warehouse_product_batch_id', __('production::app.wpbWrongCompany'));
            }

            if ((int) $wpb->warehouse_id !== (int) $order->rm_warehouse_id) {
                $validator->errors()->add('warehouse_product_batch_id', __('production::app.wpbWrongWarehouse'));
            }

            if ((int) $wpb->product_id !== (int) $this->input('component_product_id')) {
                $validator->errors()->add('component_product_id', __('production::app.wpbWrongProduct'));
            }
        });
    }
}
