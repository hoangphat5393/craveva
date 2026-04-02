<?php

namespace Modules\Warehouse\Entities;

use App\Models\BaseModel;

class ProductUnitConversion extends BaseModel
{
    protected $table = 'product_unit_conversions';

    protected $fillable = [
        'company_id',
        'product_id',
        'unit_id',
        'factor_to_base',
    ];
}
