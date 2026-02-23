<?php

namespace Modules\Pricing\Entities;

use App\Models\BaseModel;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasCompany;

class CompanyCustomerPricing extends BaseModel
{
    use HasCompany;

    protected $table = 'company_customer_pricing';

    protected $fillable = [
        'company_id',
        'client_id',
        'pricing_tier_id',
        'custom_discount_type',
        'custom_discount_value',
        'is_active',
        'valid_from',
        'valid_to',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(PricingTier::class, 'pricing_tier_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(CompanyCustomerProductPricing::class, 'company_customer_pricing_id');
    }
}
