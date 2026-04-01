<?php

namespace Modules\Purchase\Entities;

use App\Models\BaseModel;
use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class PurchaseInventory extends BaseModel
{
    use CustomFieldsTrait, HasCompany, HasFactory, Notifiable;

    const FILE_PATH = 'inventory';

    protected $dates = ['date', 'created_at', 'updated_at'];

    protected $table = 'purchase_inventory_adjustment';

    protected $with = [];

    const CUSTOM_FIELD_MODEL = 'Modules\\Purchase\\Entities\\PurchaseInventory';

    public function getImageUrlAttribute()
    {
        if (app()->environment(['development', 'demo']) && str_contains($this->default_image, 'http')) {
            return $this->default_image;
        }

        return ($this->default_image) ? asset_url_local_s3(PurchaseInventory::FILE_PATH . '/' . $this->default_image) : '';
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(PurchaseStockAdjustment::class, 'inventory_id')->orderByDesc('id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(PurchaseInventoryFile::class, 'inventory_id')->orderByDesc('id');
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(PurchaseStockAdjustmentReason::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(\Modules\Warehouse\Entities\Warehouse::class, 'warehouse_id');
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(PurchaseProduct::class, 'purchase_stock_adjustments', 'inventory_id', 'product_id')
            ->withPivot(['quantity_adjustment', 'net_quantity', 'reserved_quantity', 'type', 'changed_value', 'adjusted_value', 'expiration_date', 'manufacturing_date'])
            ->withTimestamps();
    }
}
