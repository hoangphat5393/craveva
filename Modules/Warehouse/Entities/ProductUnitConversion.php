<?php

namespace Modules\Warehouse\Entities;

use App\Models\BaseModel;
use App\Models\UnitType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUnitConversion extends BaseModel
{
    protected $table = 'product_unit_conversions';

    protected $fillable = [
        'company_id',
        'product_id',
        'unit_id',
        'factor_to_base',
        'selling_price',
        'for_sale',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'factor_to_base' => 'float',
            'selling_price' => 'float',
            'for_sale' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_id');
    }
}
