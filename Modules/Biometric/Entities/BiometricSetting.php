<?php

namespace Modules\Biometric\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;

class BiometricSetting extends BaseModel
{
    use HasCompany;

    protected $guarded = ['id'];
}
