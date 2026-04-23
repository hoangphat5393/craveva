<?php

namespace Modules\Purchase\Entities;

use App\Models\BaseModel;
use App\Models\OrderItems;
use App\Models\UnitType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Warehouse\Entities\WarehouseProductBatch;

class SalesDoItem extends BaseModel
{
    protected $table = 'sales_do_items';

    protected $fillable = [
        'sales_do_id',
        'legacy_sales_shipment_item_id',
        'order_item_id',
        'product_id',
        'quantity_ordered',
        'quantity_shipped',
        'unit_id',
        'warehouse_batch_id',
        'batch_number',
        'expiration_date',
    ];

    protected $casts = [
        'expiration_date' => 'date',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(SalesDo::class, 'sales_do_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItems::class, 'order_item_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_id');
    }

    public function warehouseBatch(): BelongsTo
    {
        return $this->belongsTo(WarehouseProductBatch::class, 'warehouse_batch_id');
    }

    public function getBatchDisplayAttribute(): string
    {
        if (! empty($this->batch_number)) {
            return (string) $this->batch_number;
        }

        if (! empty($this->warehouseBatch?->batch_number)) {
            return (string) $this->warehouseBatch->batch_number;
        }

        if (! empty($this->warehouse_batch_id)) {
            return 'Batch#'.$this->warehouse_batch_id;
        }

        return '—';
    }
}
