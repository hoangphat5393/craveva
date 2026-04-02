<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Purchase\Entities\DeliveryOrderItem;
use Modules\Warehouse\Entities\Warehouse;

class StockMovement extends BaseModel
{
    use HasCompany;

    protected $table = 'stock_movements';

    protected $fillable = [
        'company_id',
        'product_id',
        'delivery_order_item_id',
        'movement_type',
        'warehouse_from_id',
        'warehouse_to_id',
        'warehouse_location_from_id',
        'warehouse_location_to_id',
        'batch_number',
        'expiry_date',
        'quantity',
        'unit_id',
        'fefo_fifo_rule',
        'reference_type',
        'reference_id',
        'idempotency_key',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function deliveryItem(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrderItem::class, 'delivery_order_item_id');
    }

    public function warehouseFrom(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_from_id');
    }

    public function warehouseTo(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_to_id');
    }
}
