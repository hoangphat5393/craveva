<?php

namespace Modules\Purchase\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseStockAdjustment extends BaseModel
{
    use HasCompany, HasFactory;

    protected $table = 'purchase_stock_adjustments';

    protected $fillable = ['warehouse_id', 'batch_number', 'manufacturing_date', 'expiration_date', 'inventory_id', 'product_id', 'reason_id', 'type', 'date', 'reference_number', 'net_quantity', 'reserved_quantity', 'quantity_adjustment', 'description', 'status', 'changed_value', 'adjusted_value'];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiration_date' => 'date',
        'net_quantity' => 'float',
        'reserved_quantity' => 'float',
        'quantity_adjustment' => 'float',
        'changed_value' => 'float',
        'adjusted_value' => 'float',
    ];

    /**
     * Derived from expiration_date (same rules as inventory filters: expired / within 30 days / normal).
     */
    public function getNearExpiryStatusAttribute(): ?string
    {
        if (empty($this->expiration_date)) {
            return null;
        }

        $exp = Carbon::parse($this->expiration_date)->startOfDay();
        $today = now()->startOfDay();

        if ($exp->lt($today)) {
            return 'expired';
        }

        $threshold = (int) config('purchase.inventory_near_expiry_days', 30);
        if ($exp->lte($today->copy()->addDays($threshold))) {
            return 'near_expiry';
        }

        return 'normal';
    }

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
