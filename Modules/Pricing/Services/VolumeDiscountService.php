<?php

namespace Modules\Pricing\Services;

use Modules\Pricing\Entities\VolumeDiscountRule;

class VolumeDiscountService
{
    /**
     * Selects the first matching active rule after ordering by minimum_quantity DESC, then id ASC.
     * This preserves existing business behavior and is not a "maximum discount wins" strategy.
     */
    public function calculate(array $items, ?int $contextCompanyId = null): array
    {
        if (empty($items)) {
            return ['value' => 0];
        }

        $companyId = $contextCompanyId ?? (function_exists('company') && company() ? company()->id : null);
        $rules = VolumeDiscountRule::query()
            ->where('is_active', true)
            ->where(function ($q) use ($companyId) {
                if ($companyId) {
                    $q->whereNull('company_id')->orWhere('company_id', $companyId);

                    return;
                }

                $q->whereNull('company_id');
            })
            ->orderByDesc('minimum_quantity')
            ->orderBy('id')
            ->get();

        $totalDiscount = 0.0;

        foreach ($items as $item) {
            $quantity = (int) ($item['quantity'] ?? 0);
            $price = (float) ($item['price'] ?? 0);

            if ($quantity <= 0 || $price <= 0) {
                continue;
            }

            $productId = $item['product_id'] ?? null;

            $rule = $rules->first(function ($rule) use ($productId, $quantity) {
                if ((int) $rule->minimum_quantity > $quantity) {
                    return false;
                }

                if ($rule->maximum_quantity !== null && (int) $rule->maximum_quantity < $quantity) {
                    return false;
                }

                if ($rule->applies_to_type === 'all') {
                    return true;
                }

                return $productId
                    && $rule->applies_to_type === 'products'
                    && (int) $rule->applies_to_product_id === (int) $productId;
            });

            if (! $rule) {
                continue;
            }

            $lineTotal = $price * $quantity;
            $discount = 0.0;

            if ($rule->discount_type === 'percentage' && $rule->discount_value !== null) {
                $discount = $lineTotal * ((float) $rule->discount_value / 100);
            } elseif ($rule->discount_type === 'fixed_amount' && $rule->discount_value !== null) {
                $discount = (float) $rule->discount_value;
            }

            if ($discount > 0) {
                $totalDiscount += $discount;
            }
        }

        if ($totalDiscount <= 0) {
            return ['value' => 0];
        }

        return ['value' => round($totalDiscount, 2)];
    }
}
