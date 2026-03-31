<?php

namespace Modules\Purchase\Entities;

use App\Models\BaseModel;
use App\Models\Grn;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrnItem extends BaseModel
{
    protected $table = 'grn_items';

    protected $fillable = [
        'grn_id',
        'legacy_delivery_order_item_id',
        'purchase_item_id',
        'product_id',
        'batch_number',
        'expiry_date',
        'picking_rule_applied',
        'quantity_ordered',
        'quantity_received',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(Grn::class, 'grn_id');
    }

    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class, 'purchase_item_id');
    }
}
