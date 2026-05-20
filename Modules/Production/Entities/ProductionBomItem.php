<?php

namespace Modules\Production\Entities;

use App\Models\BaseModel;
use App\Models\Product;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionBomItem extends BaseModel
{
    use HasCompany;

    protected $table = 'production_bom_items';

    protected $fillable = [
        'company_id',
        'production_bom_id',
        'component_product_id',
        'quantity',
        'waste_percent',
        'unit_id',
        'yield_factor',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'waste_percent' => 'float',
            'yield_factor' => 'float',
            'sort_order' => 'integer',
        ];
    }

    public function bom(): BelongsTo
    {
        return $this->belongsTo(ProductionBom::class, 'production_bom_id');
    }

    public function componentProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'component_product_id');
    }
}
