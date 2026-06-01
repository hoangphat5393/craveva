<?php

declare(strict_types=1);

namespace Modules\Warehouse\Services;

use App\Enums\ProductType;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Modules\Purchase\Entities\PurchaseProduct;
use Modules\Warehouse\Entities\ProductUnitConversion;

class ProductUnitConversionSyncService
{
    /**
     * @return list<array{unit_id:int, factor_to_base:float, selling_price:?float, for_sale:bool, sort_order:int}>
     */
    public function parseRowsFromRequest(Request $request, int $baseUnitId): array
    {
        $unitIds = $request->input('unit_conversion_unit_id', []);
        $factors = $request->input('unit_conversion_factor', []);
        $prices = $request->input('unit_conversion_selling_price', []);
        $forSales = $request->input('unit_conversion_for_sale', []);

        if (! is_array($unitIds) || $unitIds === []) {
            return [];
        }

        $rows = [];
        $seenUnitIds = [];

        foreach ($unitIds as $index => $unitIdRaw) {
            $unitId = (int) $unitIdRaw;
            if ($unitId <= 0) {
                continue;
            }

            if ($unitId === $baseUnitId) {
                throw new InvalidArgumentException(__('purchase::messages.unitConversionCannotMatchBase'));
            }

            if (isset($seenUnitIds[$unitId])) {
                throw new InvalidArgumentException(__('purchase::messages.unitConversionDuplicateUnit'));
            }

            $seenUnitIds[$unitId] = true;

            $factor = isset($factors[$index]) ? (float) $factors[$index] : 0.0;
            if ($factor <= 0) {
                throw new InvalidArgumentException(__('purchase::messages.unitConversionFactorInvalid'));
            }

            $priceRaw = $prices[$index] ?? null;
            $sellingPrice = ($priceRaw !== null && $priceRaw !== '') ? round((float) $priceRaw, 4) : null;

            $forSale = isset($forSales[$index]) && (string) $forSales[$index] === '1';

            $rows[] = [
                'unit_id' => $unitId,
                'factor_to_base' => $factor,
                'selling_price' => $sellingPrice,
                'for_sale' => $forSale,
                'sort_order' => count($rows),
            ];
        }

        return $rows;
    }

    /**
     * @param  list<array{unit_id:int, factor_to_base:float, selling_price:?float, for_sale:bool, sort_order:int}>  $rows
     */
    /**
     * @param  Product|PurchaseProduct  $product  Same `products` row; Purchase module uses PurchaseProduct.
     */
    public function sync(Product|PurchaseProduct $product, array $rows): void
    {
        if (! Schema::hasTable('product_unit_conversions')) {
            return;
        }

        [$companyId, $productId] = $this->productScopeIds($product);

        DB::transaction(function () use ($companyId, $productId, $rows): void {
            ProductUnitConversion::query()
                ->where('company_id', $companyId)
                ->where('product_id', $productId)
                ->delete();

            foreach ($rows as $row) {
                ProductUnitConversion::query()->create([
                    'company_id' => $companyId,
                    'product_id' => $productId,
                    'unit_id' => $row['unit_id'],
                    'factor_to_base' => $row['factor_to_base'],
                    'selling_price' => $row['selling_price'],
                    'for_sale' => $row['for_sale'],
                    'sort_order' => $row['sort_order'],
                ]);
            }
        });
    }

    public function syncFromRequest(Product|PurchaseProduct $product, Request $request): void
    {
        $type = (string) ($product->type ?? $request->input('type', ''));

        if (! ProductType::supportsAlternateUnitConversions($type)) {
            $this->sync($product, []);

            return;
        }

        $baseUnitId = (int) ($product->unit_id ?? $request->input('unit_type', 0));
        $rows = $this->parseRowsFromRequest($request, $baseUnitId);
        $this->sync($product, $rows);
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function productScopeIds(Product|PurchaseProduct $product): array
    {
        if (! $product->id || ! $product->company_id) {
            throw new InvalidArgumentException('Product must be saved before syncing unit conversions.');
        }

        return [(int) $product->company_id, (int) $product->id];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function rowsForProduct(int $companyId, int $productId): array
    {
        if (! Schema::hasTable('product_unit_conversions')) {
            return [];
        }

        return ProductUnitConversion::query()
            ->where('company_id', $companyId)
            ->where('product_id', $productId)
            ->with('unit:id,unit_type')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(static function (ProductUnitConversion $row): array {
                return [
                    'unit_id' => (int) $row->unit_id,
                    'unit_label' => $row->unit?->unit_type ?? '',
                    'factor_to_base' => (float) $row->factor_to_base,
                    'selling_price' => $row->selling_price !== null ? (float) $row->selling_price : null,
                    'for_sale' => (bool) $row->for_sale,
                ];
            })
            ->values()
            ->all();
    }
}
