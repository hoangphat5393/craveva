<?php

namespace Modules\Purchase\Entities;

use App\Models\BaseModel;
use App\Models\DeliveryOrder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryOrderItem extends BaseModel
{
    protected $table = 'delivery_order_items';

    protected $fillable = [
        'delivery_order_id',
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
        return $this->belongsTo(DeliveryOrder::class, 'delivery_order_id');
    }

    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class, 'purchase_item_id');
    }
}
