<?php

declare(strict_types=1);

namespace Modules\Warehouse\Services;

use App\Models\Product;
use App\Models\UnitType;

class ProductUnitQuantityHintService
{
    public function __construct(
        protected WarehouseUnitConversionService $unitConversionService
    ) {}

    /**
     * Human-readable hint: entered qty in line UOM ≈ qty in product base UOM.
     */
    public function hint(int $companyId, int $productId, float $quantity, ?int $lineUnitId): ?string
    {
        if ($quantity <= 0 || $lineUnitId === null || $lineUnitId <= 0) {
            return null;
        }

        $baseUnitId = (int) (Product::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('id', $productId)
            ->value('unit_id') ?? 0);

        if ($baseUnitId <= 0 || $lineUnitId === $baseUnitId) {
            return null;
        }

        $baseQty = $this->unitConversionService->convertToBase(
            $companyId,
            $productId,
            $quantity,
            $lineUnitId,
        );

        if (abs($baseQty - $quantity) < 0.0000001) {
            return null;
        }

        $baseLabel = UnitType::query()->where('id', $baseUnitId)->value('unit_type');

        return __('purchase::app.productUnitQuantityBaseHint', [
            'qty' => rtrim(rtrim(number_format($baseQty, 4, '.', ''), '0'), '.'),
            'unit' => $baseLabel ? ucwords((string) $baseLabel) : '',
        ]);
    }
}
