<?php

namespace Modules\Pricing\Entities;

use App\Models\BaseModel;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingTierItem extends BaseModel
{
    protected $table = 'pricing_tier_items';

    protected $fillable = [
        'pricing_tier_id',
        'product_id',
        'discount_type',
        'discount_value',
        'is_active',
    ];

    public function tier(): BelongsTo
    {
        return $this->belongsTo(PricingTier::class, 'pricing_tier_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
