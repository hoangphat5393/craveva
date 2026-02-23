<?php

namespace Modules\Warehouse\Entities;

use App\Models\BaseModel;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseProductStock extends BaseModel
{
    use HasFactory;

    protected $table = 'warehouse_product_stock';

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'quantity',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
