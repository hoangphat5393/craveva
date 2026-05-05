<?php

namespace Modules\Production\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Production\Entities\ProductionBatch;
use Modules\Production\Entities\ProductionOrder;
use Modules\Production\Support\ProductionTenantAccess;

class StoreProductionBatchOutputRequest extends FormRequest
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
        $companyId = (int) (company()?->id ?? 0);

        return [
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'batch_number' => ['required', 'string', 'max:191'],
            'warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')->where('company_id', $companyId)],
            'expiration_date' => ['nullable', 'date'],
            'manufacturing_date' => ['nullable', 'date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            /** @var ProductionBatch|null $batch */
            $batch = $this->route('batch');
            if (! $batch instanceof ProductionBatch) {
                return;
            }

            $order = $batch->order;
            if (! $order instanceof ProductionOrder) {
                return;
            }

            if ($batch->posted_consumptions_at === null) {
                $validator->errors()->add('batch', __('production::app.fgRequiresConsumptionPosted'));
            }

            if ($batch->posted_receipt_at !== null) {
                $validator->errors()->add('batch', __('production::app.fgBatchAlreadyReceived'));
            }

            if ($order->status === ProductionOrder::STATUS_COMPLETED) {
                $validator->errors()->add('order', __('production::app.fgOrderAlreadyCompleted'));
            }
        });
    }
}
