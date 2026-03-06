<?php

namespace Modules\Purchase\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;

class PurchaseVendorCategory extends BaseModel
{
    use HasCompany;

    protected $table = 'purchase_vendor_categories';
}
