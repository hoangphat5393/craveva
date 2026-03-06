<?php

namespace App\Models\SuperAdmin;

use App\Models\BaseModel;
use App\Models\Company;

class PaystackSubscription extends BaseModel
{
    protected $dates = ['created_at'];

    protected $casts = ['created_at'];

    protected $table = 'paystack_subscriptions';

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
