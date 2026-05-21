<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Warehouse\Services\ProductSellableUnitsService;
use Modules\Warehouse\Services\ProductUnitPriceResolver;

class DocumentLineUnitPricing
{
    /**
     * @return list<array{unit_id:int, label:string, is_base:bool, factor_to_base:float, unit_price:string}>
     */
    public static function sellableUnitsForLine(
        Product $product,
        ?Currency $currency,
        mixed $requestExchangeRate,
        bool $forSaleOnly = true
    ): array {
        if (! Schema::hasTable('product_unit_conversions') || ! class_exists(ProductSellableUnitsService::class)) {
            return [];
        }

        $companyId = (int) $product->company_id;
        $rows = app(ProductSellableUnitsService::class)->sellableUnits($companyId, (int) $product->id, $forSaleOnly);
        $priced = [];

        foreach ($rows as $row) {
            $priced[] = array_merge($row, [
                'unit_price' => OrderProductUnitPrice::formatForOrder(
                    $product,
                    (int) $row['unit_id'],
                    $currency,
                    $requestExchangeRate
                ),
            ]);
        }

        return $priced;
    }

    /**
     * PO / mua hàng: mọi đơn vị đã map (không lọc for_sale).
     *
     * @return list<array{unit_id:int, label:string, is_base:bool, factor_to_base:float, unit_price:string}>
     */
    public static function purchasableUnitsForLine(
        Product $product,
        ?Currency $currency,
        mixed $requestExchangeRate
    ): array {
        if (! Schema::hasTable('product_unit_conversions') || ! class_exists(ProductSellableUnitsService::class)) {
            return [];
        }

        $companyId = (int) $product->company_id;
        $rows = app(ProductSellableUnitsService::class)->sellableUnits($companyId, (int) $product->id, false);
        $priced = [];

        foreach ($rows as $row) {
            $raw = app(ProductUnitPriceResolver::class)->resolvePurchasePrice(
                $companyId,
                (int) $product->id,
                (int) $row['unit_id'],
            ) ?? 0.0;

            $priced[] = array_merge($row, [
                'unit_price' => self::formatPurchasePrice($product, $raw, $currency, $requestExchangeRate),
            ]);
        }

        return $priced;
    }

    public static function formatPurchasePrice(
        Product $product,
        float $rawPrice,
        ?Currency $currency,
        mixed $requestExchangeRate
    ): string {
        $price = $rawPrice;

        if ($currency !== null && $currency->exchange_rate !== null) {
            $exRate = (float) $currency->exchange_rate;
            if ($product->total_amount != '') {
                $price = floor((float) $product->total_amount * $exRate);
            } else {
                $price = $rawPrice * $exRate;
            }
        } elseif ($product->total_amount != '') {
            $price = (float) $product->total_amount;
        }

        return number_format((float) $price, 2, '.', '');
    }

    /**
     * @return array<int, list<array{unit_id:int, label:string, is_base:bool, factor_to_base:float, unit_price:string}>>
     */
    public static function sellableUnitsMapForOrderItems(
        iterable $items,
        ?Currency $currency,
        bool $forSaleOnly = true
    ): array {
        if (! Schema::hasTable('product_unit_conversions') || ! class_exists(ProductSellableUnitsService::class)) {
            return [];
        }

        $map = [];
        $productIds = collect($items)
            ->pluck('product_id')
            ->filter(static fn ($id) => $id !== null && (int) $id > 0)
            ->unique()
            ->map(static fn ($id): int => (int) $id);

        foreach ($productIds as $productId) {
            $product = Product::find($productId);
            if ($product === null) {
                continue;
            }

            $priced = self::sellableUnitsForLine($product, $currency, null, $forSaleOnly);
            if (count($priced) > 1) {
                $map[$productId] = $priced;
            }
        }

        return $map;
    }

    /**
     * @param  Collection<int, OrderItems>|\Illuminate\Database\Eloquent\Collection  $items
     * @return array<int, list<array{unit_id:int, label:string, is_base:bool, factor_to_base:float, unit_price:string}>>
     */
    public static function sellableUnitsMapForOrder(Order $order, bool $forSaleOnly = true): array
    {
        $currency = Currency::find($order->currency_id) ?? Currency::find(company()->currency_id);

        return self::sellableUnitsMapForOrderItems($order->items, $currency, $forSaleOnly);
    }
}
