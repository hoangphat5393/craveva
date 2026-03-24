<?php

namespace Modules\Warehouse\Entities;

use App\Models\BaseModel;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReservation extends BaseModel
{
    protected $table = 'stock_reservations';

    protected $fillable = [
        'company_id',
        'warehouse_id',
        'product_id',
        'batch_number',
        'expiration_date',
        'reserved_quantity',
        'reference_type',
        'reference_id',
        'status',
    ];

    protected $casts = [
        'expiration_date' => 'date',
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
