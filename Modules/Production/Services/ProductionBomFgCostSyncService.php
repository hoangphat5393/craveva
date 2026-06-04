<?php

declare(strict_types=1);

namespace Modules\Production\Services;

use App\Models\Product;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Support\ProductionBomLineCostCalculator;

final class ProductionBomFgCostSyncService
{
    public function __construct(
        private readonly ProductionBomLineCostCalculator $costCalculator,
    ) {}

    /**
     * Sync FG catalog cost from saved BOM lines when tenant flag and product Custom are on.
     */
    public function syncOutputProductFromBom(ProductionBom $bom, int $companyId): bool
    {
        if (! (bool) config('production.cost_sync.bom_drives_fg_purchase_price', false)) {
            return false;
        }

        $bom->loadMissing(['items']);

        $product = Product::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('id', (int) $bom->output_product_id)
            ->first();

        if ($product === null || ! (bool) $product->cost_from_bom) {
            return false;
        }

        $summary = $this->costCalculator->summarizeSavedLines($bom, $companyId);
        $total = $summary['total'] ?? null;

        if ($total === null) {
            return false;
        }

        $product->purchase_price = (string) $total;
        $product->saveQuietly();

        return true;
    }
}
