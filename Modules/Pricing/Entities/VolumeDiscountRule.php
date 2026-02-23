<?php

namespace Modules\Pricing\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Product;
use App\Traits\HasCompany;

class VolumeDiscountRule extends BaseModel
{
    use HasCompany;

    protected $table = 'volume_discount_rules';

    protected $fillable = [
        'company_id',
        'pricing_tier_id',
        'name',
        'discount_type',
        'minimum_quantity',
        'maximum_quantity',
        'discount_value',
        'applies_to_product_id',
        'applies_to_category_id',
        'applies_to_type',
        'is_active',
    ];

    public function tier(): BelongsTo
    {
        return $this->belongsTo(PricingTier::class, 'pricing_tier_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'applies_to_product_id');
    }
}
