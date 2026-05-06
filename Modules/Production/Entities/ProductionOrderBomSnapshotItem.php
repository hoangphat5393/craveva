<?php

declare(strict_types=1);

namespace Modules\Production\Entities;

use App\Models\BaseModel;
use App\Models\Product;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionOrderBomSnapshotItem extends BaseModel
{
    use HasCompany;

    protected $table = 'production_order_bom_snapshot_items';

    protected $fillable = [
        'company_id',
        'production_order_id',
        'component_product_id',
        'quantity_per_fg_unit',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity_per_fg_unit' => 'float',
            'sort_order' => 'integer',
        ];
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function componentProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'component_product_id');
    }
}
