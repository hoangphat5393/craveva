<?php

declare(strict_types=1);

namespace App\Services\Estimates;

use App\Models\Estimate;

final class EstimateRecipeMarginSummary
{
    /**
     * @return array{
     *     unit_bom_cost: float,
     *     order_quantity: float,
     *     extended_bom_cost: float,
     *     commercial_sub_total: float,
     *     gross_margin_amount: float|null,
     *     gross_margin_percent: float|null,
     *     has_bom_lines: bool
     * }
     */
    public function summarize(Estimate $estimate): array
    {
        $estimate->loadMissing(['bomLines', 'items']);

        $unitBomCost = round((float) $estimate->bomLines->sum('line_total'), 4);
        $orderQuantity = $this->resolveOrderQuantity($estimate);
        $extendedBomCost = round($unitBomCost * ($orderQuantity > 0 ? $orderQuantity : 1.0), 2);
        $commercialSubTotal = round((float) $estimate->sub_total, 2);

        $grossMarginAmount = null;
        $grossMarginPercent = null;

        if ($commercialSubTotal > 0 && $unitBomCost > 0) {
            $grossMarginAmount = round($commercialSubTotal - $extendedBomCost, 2);
            $grossMarginPercent = round(($grossMarginAmount / $commercialSubTotal) * 100, 2);
        }

        return [
            'unit_bom_cost' => $unitBomCost,
            'order_quantity' => $orderQuantity,
            'extended_bom_cost' => $extendedBomCost,
            'commercial_sub_total' => $commercialSubTotal,
            'gross_margin_amount' => $grossMarginAmount,
            'gross_margin_percent' => $grossMarginPercent,
            'has_bom_lines' => $estimate->bomLines->isNotEmpty(),
        ];
    }

    private function resolveOrderQuantity(Estimate $estimate): float
    {
        $sum = (float) $estimate->items->where('type', 'item')->sum('quantity');

        return max(0.0, $sum);
    }
}
