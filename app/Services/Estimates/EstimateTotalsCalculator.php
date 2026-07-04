<?php

namespace App\Services\Estimates;

use App\Models\Estimate;
use App\Models\Tax;

class EstimateTotalsCalculator
{
    /**
     * @param  array<int, array{amount: float, taxes?: array<int|string>|null}>  $lineItems
     * @return array{sub_total: float, discount_amount: float, tax_total: float, total: float, taxes: array<string, float>}
     */
    public function calculate(
        array $lineItems,
        float $discountValue,
        string $discountType,
        string $calculateTax = 'after_discount',
    ): array {
        $subTotal = 0.0;

        foreach ($lineItems as $line) {
            $subTotal += round((float) ($line['amount'] ?? 0), 2);
        }

        $subTotal = round($subTotal, 2);

        $discountAmount = 0.0;

        if ($discountValue > 0) {
            if ($discountType === 'percent') {
                $discountAmount = round(($subTotal / 100) * $discountValue, 2);
            } else {
                $discountAmount = round($discountValue, 2);
            }
        }

        $taxList = [];

        foreach ($lineItems as $line) {
            $amount = round((float) ($line['amount'] ?? 0), 2);
            $taxIds = $line['taxes'] ?? [];

            if ($amount <= 0 || ! is_array($taxIds) || $taxIds === []) {
                continue;
            }

            foreach ($taxIds as $taxId) {
                $tax = Tax::query()->withTrashed()->find($taxId);

                if (! $tax) {
                    continue;
                }

                $taxKey = $tax->tax_name.': '.$tax->rate_percent.'%';

                if ($calculateTax === 'after_discount' && $discountAmount > 0 && $subTotal > 0) {
                    $taxValue = ($amount - ($amount / $subTotal) * $discountAmount) * ($tax->rate_percent / 100);
                } else {
                    $taxValue = $amount * ($tax->rate_percent / 100);
                }

                $taxList[$taxKey] = ($taxList[$taxKey] ?? 0.0) + $taxValue;
            }
        }

        $taxTotal = 0.0;

        foreach ($taxList as $taxAmount) {
            $taxTotal += round((float) $taxAmount, 2);
        }

        $taxTotal = round($taxTotal, 2);
        $totalAfterDiscount = max(0.0, round($subTotal - $discountAmount, 2));
        $total = round($totalAfterDiscount + $taxTotal, 2);

        return [
            'sub_total' => $subTotal,
            'discount_amount' => $discountAmount,
            'tax_total' => $taxTotal,
            'total' => $total,
            'taxes' => array_map(static fn (float $value): float => round($value, 2), $taxList),
        ];
    }

    /**
     * @return array{sub_total: float, discount_amount: float, tax_total: float, total: float, taxes: array<string, float>}
     */
    public function calculateForEstimate(Estimate $estimate): array
    {
        $lineItems = [];

        $items = $estimate->relationLoaded('items')
            ? $estimate->items->where('type', 'item')->sortBy('field_order')
            : $estimate->items()->where('type', 'item')->orderBy('field_order')->get();

        foreach ($items as $item) {
            $lineItems[] = [
                'amount' => (float) $item->amount,
                'taxes' => $item->taxes ? json_decode($item->taxes, true) : [],
            ];
        }

        return $this->calculate(
            $lineItems,
            (float) $estimate->discount,
            (string) ($estimate->discount_type ?: 'percent'),
            (string) ($estimate->calculate_tax ?: 'after_discount'),
        );
    }

    public function totalsAreOutOfSync(Estimate $estimate, ?array $calculated = null): bool
    {
        $calculated ??= $this->calculateForEstimate($estimate);

        return abs((float) $estimate->sub_total - $calculated['sub_total']) > 0.009
            || abs((float) $estimate->total - $calculated['total']) > 0.009;
    }

    public function syncEstimateTotals(Estimate $estimate): Estimate
    {
        $calculated = $this->calculateForEstimate($estimate);
        $estimate->sub_total = $calculated['sub_total'];
        $estimate->total = $calculated['total'];
        $estimate->save();

        return $estimate;
    }

    /**
     * @param  array<int, array{quantity: float, unit_price: float}>  $lineItems
     * @return array<int, array{quantity: float, unit_price: float, amount: float}>
     */
    public function normalizeLineAmounts(array $lineItems): array
    {
        foreach ($lineItems as $index => $line) {
            $lineItems[$index]['amount'] = round((float) $line['quantity'] * (float) $line['unit_price'], 2);
        }

        return $lineItems;
    }
}
