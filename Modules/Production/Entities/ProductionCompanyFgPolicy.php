<?php

namespace Modules\Production\Entities;

use App\Models\BaseModel;

class ProductionCompanyFgPolicy extends BaseModel
{
    protected $table = 'production_company_fg_policies';

    protected $fillable = [
        'company_id',
        'policy_mode',
        'tolerance_percent',
        'tolerance_absolute',
        'controlled_require_reason_beyond_tolerance',
        'controlled_block_beyond_tolerance',
    ];

    protected function casts(): array
    {
        return [
            'tolerance_percent' => 'float',
            'tolerance_absolute' => 'float',
            'controlled_require_reason_beyond_tolerance' => 'boolean',
            'controlled_block_beyond_tolerance' => 'boolean',
        ];
    }
}
