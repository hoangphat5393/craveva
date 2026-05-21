<?php

declare(strict_types=1);

namespace Modules\Production\Support;

use App\Models\Product;
use Illuminate\Support\Collection;
use Modules\Warehouse\Services\ProductSellableUnitsService;
use Modules\Warehouse\Services\ProductUnitPriceResolver;

final class ProductionBomComponentUnitOptions
{
    public function __construct(
        private readonly ProductSellableUnitsService $sellableUnitsService,
        private readonly ProductUnitPriceResolver $priceResolver,
    ) {}

    /**
     * @param  Collection<int, Product>  $componentProducts
     * @return array<string, list<array{unit_id: int, label: string, is_base: bool, factor_to_base: float}>>
     */
    public function unitsByProductId(Collection $componentProducts, int $companyId): array
    {
        $map = [];

        foreach ($componentProducts as $product) {
            $productId = (int) $product->id;
            $map[(string) $productId] = $this->sellableUnitsService->sellableUnits(
                $companyId,
                $productId,
                false,
            );
        }

        return $map;
    }

    /**
     * @param  Collection<int, Product>  $componentProducts
     * @return array<string, array<string, float|null>>
     */
    public function unitCostByProductAndUnit(Collection $componentProducts, int $companyId): array
    {
        $map = [];

        foreach ($componentProducts as $product) {
            $productId = (int) $product->id;
            $productKey = (string) $productId;
            $map[$productKey] = [];

            foreach ($this->sellableUnitsService->sellableUnits($companyId, $productId, false) as $unit) {
                $unitId = (int) $unit['unit_id'];
                $map[$productKey][(string) $unitId] = $this->priceResolver->resolvePurchasePrice(
                    $companyId,
                    $productId,
                    $unitId,
                );
            }
        }

        return $map;
    }

    public function isAllowedUnit(int $companyId, int $productId, mixed $unitId): bool
    {
        if ($unitId === null || $unitId === '') {
            return true;
        }

        $unitId = (int) $unitId;
        if ($unitId <= 0) {
            return false;
        }

        foreach ($this->sellableUnitsService->sellableUnits($companyId, $productId, false) as $unit) {
            if ((int) $unit['unit_id'] === $unitId) {
                return true;
            }
        }

        return false;
    }

    public function defaultUnitIdForProduct(int $companyId, int $productId): ?int
    {
        $units = $this->sellableUnitsService->sellableUnits($companyId, $productId, false);

        foreach ($units as $unit) {
            if ($unit['is_base']) {
                return (int) $unit['unit_id'];
            }
        }

        return $units !== [] ? (int) $units[0]['unit_id'] : null;
    }
}
