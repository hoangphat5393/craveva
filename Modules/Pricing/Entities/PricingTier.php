<?php

namespace Modules\Pricing\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingTier extends BaseModel
{
    use HasCompany;

    protected $table = 'pricing_tiers';

    protected $fillable = [
        'name',
        'description',
        'discount_type',
        'discount_value',
        'priority',
        'valid_from',
        'valid_to',
        'is_active',
        'company_id',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PricingTierItem::class, 'pricing_tier_id');
    }
}
