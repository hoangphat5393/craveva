<?php

namespace Modules\Production\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Production\Entities\ProductionBatch;
use Modules\Production\Entities\ProductionOrder;
use Modules\Production\Services\ProductionFgQuantityPolicyService;
use Modules\Production\Support\ProductionTenantAccess;

class StoreProductionBatchOutputRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'expiration_date' => $this->normalizeDateInput($this->input('expiration_date')),
            'manufacturing_date' => $this->normalizeDateInput($this->input('manufacturing_date')),
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

        return [
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'batch_number' => ['required', 'string', 'max:191'],
            'warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')->where('company_id', $companyId)],
            'expiration_date' => ['nullable', 'date'],
            'manufacturing_date' => ['nullable', 'date'],
            'variance_reason' => ['nullable', 'string', 'max:5000'],
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

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $incomingQty = (float) ($this->input('quantity') ?? 0);

            /** @var ProductionFgQuantityPolicyService $fgPolicy */
            $fgPolicy = app(ProductionFgQuantityPolicyService::class);

            try {
                $existingTotal = $fgPolicy->registeredFgTotalForOrder($order);

                $fgPolicy->assertProjectedTotalsAllowedForOrder(
                    $order,
                    $existingTotal + $incomingQty,
                    trim((string) $this->input('variance_reason', '')),
                );
            } catch (\InvalidArgumentException $e) {
                $policyMessage = $e->getMessage();
                $validator->errors()->add('quantity', $policyMessage);

                if (
                    trim((string) $this->input('variance_reason', '')) === ''
                    && in_array($policyMessage, [
                        __('production::app.fgControlledBeyondToleranceRequiresReason'),
                        __('production::app.fgFlexibleOverPlannedRequiresReason'),
                    ], true)
                ) {
                    $validator->errors()->add(
                        'variance_reason',
                        __('production::app.fgFillVarianceReasonToContinue', [
                            'planned' => $order->planned_quantity,
                            'entered' => $incomingQty,
                        ]),
                    );
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'quantity.required' => __('production::app.fgOutputQuantityRequired'),
            'quantity.numeric' => __('production::app.fgOutputQuantityNumeric'),
            'quantity.min' => __('production::app.fgOutputQuantityMin'),
            'batch_number.required' => __('production::app.fgOutputBatchNumberRequired'),
            'warehouse_id.required' => __('production::app.fgOutputWarehouseRequired'),
            'warehouse_id.exists' => __('production::app.fgOutputWarehouseInvalid'),
            'expiration_date.date' => __('production::app.fgOutputDateInvalid'),
            'manufacturing_date.date' => __('production::app.fgOutputDateInvalid'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'quantity' => __('production::app.fgQty'),
            'batch_number' => __('production::app.fgBatchNumber'),
            'warehouse_id' => __('warehouse::app.warehouse'),
            'variance_reason' => __('production::app.fgVarianceReason'),
            'expiration_date' => __('production::app.expiry'),
            'manufacturing_date' => __('production::app.mfgDate'),
        ];
    }

    protected function normalizeDateInput(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $dateValue = trim((string) $value);
        if ($dateValue === '') {
            return null;
        }

        $formats = [
            'Y-m-d',
            (string) (company()?->date_format ?? ''),
            'd/m/Y',
            'm/d/Y',
            'd-m-Y',
            'm-d-Y',
        ];

        foreach ($formats as $format) {
            if ($format === '') {
                continue;
            }

            try {
                $parsed = Carbon::createFromFormat($format, $dateValue);
                if ($parsed !== false) {
                    return $parsed->format('Y-m-d');
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return $dateValue;
    }
}
