<?php

namespace Modules\Pricing\Entities;

use App\Models\BaseModel;
use App\Models\Product;
use App\Models\User;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientProductPricing extends BaseModel
{
    use HasCompany;

    protected $table = 'client_product_pricing';

    protected $fillable = [
        'company_id',
        'client_id',
        'product_id',
        'custom_price',
        'discount_type',
        'discount_value',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
