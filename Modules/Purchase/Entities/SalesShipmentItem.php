<?php

namespace Modules\Purchase\Entities;

use App\Models\BaseModel;
use App\Models\OrderItems;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesShipmentItem extends BaseModel
{
    protected $table = 'sales_shipment_items';

    protected $fillable = [
        'sales_shipment_id',
        'order_item_id',
        'product_id',
        'quantity_ordered',
        'quantity_shipped',
        'unit_id',
        'batch_number',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(SalesShipment::class, 'sales_shipment_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItems::class, 'order_item_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(\App\Models\UnitType::class, 'unit_id');
    }
}
