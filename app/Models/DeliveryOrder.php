<?php

namespace App\Models;

use App\Traits\HasCompany;
use App\Traits\CustomFieldsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Purchase\Entities\PurchaseOrder;
use Modules\Purchase\Entities\DeliveryOrderItem;

class DeliveryOrder extends BaseModel
{
    use HasCompany, CustomFieldsTrait;

    protected $table = 'delivery_orders';

    protected $fillable = [
        'company_id',
        'purchase_order_id',
        'type',
        'delivery_number',
        'delivery_date',
        'warehouse_id',
        'status',
        'erp_shipment_reference',
        'wms_shipment_reference',
        'created_by',
        'updated_by',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryOrderItem::class, 'delivery_order_id');
    }
}
