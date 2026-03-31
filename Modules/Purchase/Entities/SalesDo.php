<?php

namespace Modules\Purchase\Entities;

use App\Models\BaseModel;
use App\Models\Order;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesDo extends BaseModel
{
    protected $table = 'sales_dos';

    protected $fillable = [
        'legacy_sales_shipment_id',
        'company_id',
        'order_id',
        'warehouse_id',
        'do_number',
        'do_date',
        'status',
        'outbound_stock_applied',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'do_date' => 'date',
        'outbound_stock_applied' => 'boolean',
    ];

    protected function shipmentNumber(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->do_number,
            set: fn($value) => ['do_number' => $value]
        );
    }

    protected function shipmentDate(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->do_date,
            set: fn($value) => ['do_date' => $value]
        );
    }

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
        return $this->hasMany('Modules\\Purchase\\Entities\\SalesDoItem', 'sales_do_id');
    }
}
