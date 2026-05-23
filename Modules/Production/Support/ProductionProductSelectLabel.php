<?php

declare(strict_types=1);

namespace Modules\Production\Support;

use App\Models\Product;

/** Labels for product select options on Production order forms (searchable select-picker). */
final class ProductionProductSelectLabel
{
    public static function forProduct(Product $product): string
    {
        $name = trim((string) $product->name);
        $sku = trim((string) ($product->sku ?? ''));

        if ($name === '') {
            $name = __('app.product').' #'.$product->id;
        }

        if ($sku !== '') {
            return $name.' ('.$sku.')';
        }

        return $name;
    }
}
