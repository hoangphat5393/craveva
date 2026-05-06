<?php

namespace Modules\Production\Entities;

use App\Models\BaseModel;
use App\Models\Product;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Warehouse\Entities\WarehouseProductBatch;

class ProductionBatchConsumption extends BaseModel
{
    use HasCompany;

    protected $table = 'production_batch_consumptions';

    protected $fillable = [
        'company_id',
        'production_batch_id',
        'component_product_id',
        'warehouse_product_batch_id',
        'planned_quantity',
        'planned_quantity_shadow',
        'shadow_basis',
        'actual_quantity',
        'unit_id',
        'line_order',
    ];

    protected function casts(): array
    {
        return [
            'planned_quantity' => 'float',
            'planned_quantity_shadow' => 'float',
            'shadow_basis' => 'array',
            'actual_quantity' => 'float',
            'line_order' => 'integer',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'production_batch_id');
    }

    public function componentProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'component_product_id');
    }

    public function warehouseProductBatch(): BelongsTo
    {
        return $this->belongsTo(WarehouseProductBatch::class, 'warehouse_product_batch_id');
    }
}
