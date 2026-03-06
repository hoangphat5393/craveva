<?php

namespace Modules\Recruit\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecruitJobCategory extends BaseModel
{
    use HasCompany, HasFactory;

    protected $fillable = [];
}
