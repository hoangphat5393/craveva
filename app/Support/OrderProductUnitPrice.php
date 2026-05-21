<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Currency;
use App\Models\Product;
use Modules\Warehouse\Services\ProductUnitPriceResolver;

class OrderProductUnitPrice
{
    /**
     * Resolve catalog UOM price and apply order currency / exchange rate (same rules as OrderController::addItem).
     */
    public static function formatForOrder(
        Product $product,
        ?int $unitId,
        ?Currency $currency,
        mixed $requestExchangeRate
    ): string {
        $companyId = (int) $product->company_id;
        $resolver = app(ProductUnitPriceResolver::class);
        $raw = $resolver->resolveSellingPrice($companyId, (int) $product->id, $unitId) ?? 0.0;

        if ($currency !== null && $currency->exchange_rate !== null) {
            $exRate = ($currency->exchange_rate == $requestExchangeRate)
                ? (float) $currency->exchange_rate
                : (float) ($requestExchangeRate ?: 1);

            if ($product->total_amount != '') {
                $raw = floor((float) $product->total_amount / $exRate);
            } else {
                $raw = $raw / $exRate;
            }
        } elseif ($product->total_amount != '') {
            $raw = (float) $product->total_amount;
        }

        return number_format((float) $raw, 2, '.', '');
    }
}
