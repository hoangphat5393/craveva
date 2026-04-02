<?php

namespace Modules\Warehouse\Entities;

use App\Models\BaseModel;
use App\Models\Product;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends BaseModel
{
    use HasCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'warehouse_type',
        'address',
        'description',
        'is_default',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(WarehouseProductStock::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'warehouse_product_stock')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function batches(): HasMany
    {
        return $this->hasMany(WarehouseProductBatch::class, 'warehouse_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(StockReservation::class, 'warehouse_id');
    }
}
