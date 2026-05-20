<?php

declare(strict_types=1);

namespace Modules\Production\Services;

use App\Enums\ProductType;
use App\Models\Order;
use Modules\Production\Entities\ProductionBom;

/**
 * @phpstan-type Prefill array{
 *     output_product_id: int|null,
 *     planned_quantity: float|null,
 *     production_bom_id: int|null,
 *     estimate_id: int|null,
 *     estimate_number: string|null,
 *     estimate_bom_line_count: int,
 *     hint: string|null,
 * }
 */
class ProductionOrderSalesOrderPrefill
{
    /**
     * @return Prefill|null
     */
    public function forSalesOrder(int $salesOrderId, int $companyId): ?array
    {
        $order = Order::query()
            ->with(['items.product', 'estimate.bomLines'])
            ->where('company_id', $companyId)
            ->find($salesOrderId);

        if ($order === null) {
            return null;
        }

        $outputProductId = null;
        $plannedQuantity = null;

        foreach ($order->items as $item) {
            $product = $item->product;
            if ($product === null || $product->type !== ProductType::Goods->value) {
                continue;
            }

            $qty = (float) ($item->quantity ?? 0);
            if ($qty <= 0) {
                continue;
            }

            if ($outputProductId === null) {
                $outputProductId = (int) $product->id;
                $plannedQuantity = $qty;
            } elseif ((int) $product->id === $outputProductId) {
                $plannedQuantity += $qty;
            }
        }

        $productionBomId = null;
        if ($outputProductId !== null) {
            $bom = ProductionBom::query()
                ->where('company_id', $companyId)
                ->where('output_product_id', $outputProductId)
                ->orderByDesc('is_default')
                ->orderByDesc('id')
                ->first(['id']);

            $productionBomId = $bom !== null ? (int) $bom->id : null;
        }

        $estimate = $order->estimate;
        $estimateBomLineCount = $estimate !== null && $estimate->relationLoaded('bomLines')
            ? $estimate->bomLines->count()
            : 0;

        $hint = null;
        if ($estimate !== null) {
            $hint = $estimateBomLineCount > 0
                ? __('production::app.prefillFromEstimateWithBom', [
                    'number' => $estimate->estimate_number ?? '#'.$estimate->id,
                    'lines' => $estimateBomLineCount,
                ])
                : __('production::app.prefillFromEstimate', [
                    'number' => $estimate->estimate_number ?? '#'.$estimate->id,
                ]);
        }

        return [
            'output_product_id' => $outputProductId,
            'planned_quantity' => $plannedQuantity,
            'production_bom_id' => $productionBomId,
            'estimate_id' => $estimate?->id,
            'estimate_number' => $estimate?->estimate_number,
            'estimate_bom_line_count' => $estimateBomLineCount,
            'hint' => $hint,
        ];
    }
}
