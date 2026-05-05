<?php

namespace Modules\Production\Entities;

use App\Models\BaseModel;
use App\Models\Product;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Warehouse\Entities\Warehouse;

class ProductionBatchOutput extends BaseModel
{
    use HasCompany;

    protected $table = 'production_batch_outputs';

    protected $fillable = [
        'company_id',
        'production_batch_id',
        'output_product_id',
        'quantity',
        'batch_number',
        'expiration_date',
        'manufacturing_date',
        'warehouse_id',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'expiration_date' => 'date',
            'manufacturing_date' => 'date',
            'posted_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'production_batch_id');
    }

    public function outputProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'output_product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
