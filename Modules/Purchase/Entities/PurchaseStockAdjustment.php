<?php

namespace Modules\Purchase\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseStockAdjustment extends BaseModel
{
    use HasCompany, HasFactory;

    protected $table = 'purchase_stock_adjustments';

    protected $fillable = ['warehouse_id', 'manufacturing_date', 'expiration_date', 'inventory_id', 'product_id', 'reason_id', 'type', 'date', 'reference_number', 'net_quantity', 'quantity_adjustment', 'description', 'status', 'changed_value', 'adjusted_value'];

    public function reason(): BelongsTo
    {
        return $this->belongsTo(PurchaseStockAdjustmentReason::class, 'reason_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(\Modules\Warehouse\Entities\Warehouse::class, 'warehouse_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(PurchaseProduct::class, 'product_id');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(PurchaseInventory::class, 'inventory_id');
    }
}
