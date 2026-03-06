<?php

namespace Modules\Recruit\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;

class RecruitSalaryStructure extends BaseModel
{
    use HasCompany;

    protected $fillable = [];
}
