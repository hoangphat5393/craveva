<?php

namespace Modules\Warehouse\Entities;

use App\Models\BaseModel;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseProductBatch extends BaseModel
{
    protected $table = 'warehouse_product_batches';

    protected $fillable = [
        'company_id',
        'warehouse_id',
        'product_id',
        'batch_number',
        'expiration_date',
        'manufacturing_date',
        'quantity',
        'reserved_quantity',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
