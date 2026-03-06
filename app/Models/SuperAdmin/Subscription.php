<?php

namespace App\Models\SuperAdmin;

use App\Models\BaseModel;
use App\Models\Company;

class Subscription extends BaseModel
{
    protected $dates = ['created_at'];

    protected $casts = ['created_at'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
