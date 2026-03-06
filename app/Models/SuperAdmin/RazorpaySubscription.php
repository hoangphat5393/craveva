<?php

namespace App\Models\SuperAdmin;

use App\Models\BaseModel;
use App\Models\Company;
use App\Models\Currency;

class RazorpaySubscription extends BaseModel
{
    protected $dates = ['created_at'];

    protected $casts = ['created_at'];

    protected $table = 'razorpay_subscriptions';

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
