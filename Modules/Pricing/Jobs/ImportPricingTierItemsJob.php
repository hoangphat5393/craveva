<?php

namespace Modules\Pricing\Jobs;

use App\Models\Product;
use App\Traits\ExcelImportable;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Pricing\Entities\PricingTier;
use Modules\Pricing\Entities\PricingTierItem;

class ImportPricingTierItemsJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ExcelImportable;

    private $row;
    private $columns;
    private $company;

    public function __construct($row, $columns, $company = null)
    {
        $this->row = $row;
        $this->columns = $columns;
        $this->company = $company;
    }

    public function handle(): void
    {
        $tierName = $this->getColumnValue('tier_name');
        $sku = $this->getColumnValue('product_sku');
        $discountType = $this->getColumnValue('discount_type');
        $discountValue = $this->getColumnValue('discount_value');

        if (empty($tierName)) {
            $this->failJobWithMessage('Tier Name missing: ');
            return;
        }

        $tierQuery = PricingTier::where('name', $tierName);
        if ($this->company) {
            $tierQuery = $tierQuery->where('company_id', $this->company->id);
        }
        $tier = $tierQuery->first();

        if (!$tier) {
            $this->failJob('Pricing Tier not found: ');
            return;
        }

        if (empty($sku)) {
            $this->failJobWithMessage('Product SKU missing: ');
            return;
        }

        $product = Product::where('sku', $sku)->first();

        if (!$product) {
            $this->failJob('Product not found: ');
            return;
        }

        $item = PricingTierItem::firstOrNew([
            'pricing_tier_id' => $tier->id,
            'product_id' => $product->id,
        ]);

        $item->discount_type = $discountType ?: 'percentage';
        $item->discount_value = $discountValue !== null && $discountValue !== '' ? (float) $discountValue : 0;
        $item->is_active = true;
        $item->save();
    }
}
