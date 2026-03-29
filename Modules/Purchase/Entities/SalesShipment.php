<?php

namespace Modules\Purchase\Entities;

use App\Models\BaseModel;
use App\Models\Order;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesShipment extends BaseModel
{
    protected $table = 'sales_shipments';

    protected $fillable = [
        'company_id',
        'order_id',
        'warehouse_id',
        'shipment_number',
        'shipment_date',
        'status',
        'outbound_stock_applied',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'shipment_date' => 'date',
        'outbound_stock_applied' => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(\Modules\Warehouse\Entities\Warehouse::class, 'warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesShipmentItem::class, 'sales_shipment_id');
    }
}
