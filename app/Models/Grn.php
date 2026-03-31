<?php

namespace App\Models;

use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Purchase\Entities\PurchaseOrder;

class Grn extends BaseModel
{
    use CustomFieldsTrait, HasCompany;

    protected $table = 'grns';

    protected $fillable = [
        'legacy_delivery_order_id',
        'company_id',
        'purchase_order_id',
        'type',
        'grn_number',
        'grn_date',
        'warehouse_id',
        'status',
        'inbound_stock_applied',
        'erp_shipment_reference',
        'wms_shipment_reference',
        'delivery_fee',
        'created_by',
        'updated_by',
    ];

    protected function deliveryNumber(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->grn_number,
            set: fn($value) => ['grn_number' => $value]
        );
    }

    protected function deliveryDate(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->grn_date,
            set: fn($value) => ['grn_date' => $value]
        );
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany('Modules\\Purchase\\Entities\\GrnItem', 'grn_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(\Modules\Warehouse\Entities\Warehouse::class, 'warehouse_id');
    }
}
