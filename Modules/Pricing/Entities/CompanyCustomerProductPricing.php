<?php

namespace Modules\Pricing\Entities;

use App\Models\BaseModel;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyCustomerProductPricing extends BaseModel
{
    protected $table = 'company_customer_product_pricing';

    protected $fillable = [
        'company_customer_pricing_id',
        'product_id',
        'custom_price',
        'custom_discount_type',
        'custom_discount_value',
    ];

    public function companyCustomerPricing(): BelongsTo
    {
        return $this->belongsTo(CompanyCustomerPricing::class, 'company_customer_pricing_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
