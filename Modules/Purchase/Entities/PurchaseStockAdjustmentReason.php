<?php

namespace Modules\Purchase\Entities;

use App\Models\BaseModel;
use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseStockAdjustmentReason extends BaseModel
{
    use CustomFieldsTrait, HasCompany, HasFactory;

    protected $fillable = [];

    protected $table = 'purchase_stock_adjustment_reasons';

    protected static function newFactory()
    {
        return \Modules\Purchase\Database\factories\PurchaseProductFactory::new();
    }

    public function stockAdjustment(): HasMany
    {
        return $this->hasMany(PurchaseStockAdjustment::class);
    }
}
