<?php

declare(strict_types=1);

namespace Modules\Production\Support;

use App\Models\Product;
use App\Models\UnitType;
use Illuminate\Support\Collection;

final class ProductionProductUnitLabelMap
{
    /**
     * Map product id => unit label using `unit_types` by company (avoids nullable eager loads when CompanyScope hides the relation).
     *
     * @param  Collection<int, Product>  $products
     * @return Collection<string, string>
     */
    public static function forProducts(Collection $products, int $companyId): Collection
    {
        $unitIds = $products->pluck('unit_id')->filter()->unique()->values();
        $typeByUnitId = collect();
        if ($unitIds->isNotEmpty()) {
            $typeByUnitId = UnitType::withoutGlobalScopes()
                ->whereIn('id', $unitIds)
                ->where(static function ($q) use ($companyId): void {
                    $q->where('company_id', $companyId)
                        ->orWhereNull('company_id');
                })
                ->get(['id', 'unit_type', 'company_id'])
                ->filter(static function ($u) use ($companyId): bool {
                    return (int) $u->company_id === $companyId || $u->company_id === null;
                })
                ->mapWithKeys(static fn($u) => [(string) $u->id => (string) $u->unit_type]);
        }

        return $products->mapWithKeys(
            static function ($p) use ($typeByUnitId): array {
                $label = '—';
                if ($p->unit_id) {
                    $key = (string) $p->unit_id;
                    if ($typeByUnitId->has($key)) {
                        $label = (string) $typeByUnitId->get($key);
                    }
                }

                return [(string) $p->id => $label];
            },
        );
    }
}
