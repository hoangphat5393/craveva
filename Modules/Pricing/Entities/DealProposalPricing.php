<?php

namespace Modules\Pricing\Entities;

use App\Models\BaseModel;
use App\Models\Proposal;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealProposalPricing extends BaseModel
{
    protected $table = 'deal_proposal_pricing';

    protected $fillable = [
        'proposal_id',
        'pricing_tier_id',
        'applied_discount_type',
        'applied_discount_value',
        'volume_discount_applied',
        'custom_pricing_applied',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class, 'proposal_id');
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(PricingTier::class, 'pricing_tier_id');
    }
}
