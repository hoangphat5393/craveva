<?php

namespace Modules\Pricing\Services;

use Modules\Pricing\Entities\VolumeDiscountRule;

class VolumeDiscountService
{
    public function calculate(array $items, ?int $contextCompanyId = null): array
    {
        if (empty($items)) {
            return ['value' => 0];
        }

        $companyId = $contextCompanyId ?? (function_exists('company') && company() ? company()->id : null);

        $totalDiscount = 0.0;

        foreach ($items as $item) {
            $quantity = (int) ($item['quantity'] ?? 0);
            $price = (float) ($item['price'] ?? 0);

            if ($quantity <= 0 || $price <= 0) {
                continue;
            }

            $productId = $item['product_id'] ?? null;

            $query = VolumeDiscountRule::query()
                ->where('is_active', true);

            if ($companyId) {
                $query->where(function ($q) use ($companyId) {
                    $q->whereNull('company_id')->orWhere('company_id', $companyId);
                });
            }

            $query->where(function ($q) use ($productId) {
                $q->where('applies_to_type', 'all');

                if ($productId) {
                    $q->orWhere(function ($q2) use ($productId) {
                        $q2->where('applies_to_type', 'products')
                            ->where('applies_to_product_id', $productId);
                    });
                }
            });

            $query->where('minimum_quantity', '<=', $quantity)
                ->where(function ($q) use ($quantity) {
                    $q->whereNull('maximum_quantity')->orWhere('maximum_quantity', '>=', $quantity);
                })
                ->orderByDesc('minimum_quantity')
                ->orderBy('id');

            $rule = $query->first();

            if (! $rule) {
                continue;
            }

            $lineTotal = $price * $quantity;
            $discount = 0.0;

            if ($rule->discount_type === 'percentage' && $rule->discount_value) {
                $discount = $lineTotal * ((float) $rule->discount_value / 100);
            } elseif ($rule->discount_type === 'fixed_amount' && $rule->discount_value) {
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
