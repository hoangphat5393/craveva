<?php

namespace App\Models;

use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Purchase\Entities\DeliveryOrderItem;
use Modules\Purchase\Entities\PurchaseOrder;

class DeliveryOrder extends BaseModel
{
    use CustomFieldsTrait, HasCompany;

    protected $table = 'delivery_orders';

    protected $fillable = [
        'company_id',
        'purchase_order_id',
        'type',
        'delivery_number',
        'delivery_date',
        'warehouse_id',
        'status',
        'inbound_stock_applied',
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
