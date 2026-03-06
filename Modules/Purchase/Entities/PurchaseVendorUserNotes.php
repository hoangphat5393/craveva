<?php

namespace Modules\Purchase\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseVendorUserNotes extends BaseModel
{
    use HasCompany, HasFactory;

    protected $fillable = [];

    protected static function newFactory()
    {

        return \Modules\Purchase\Database\factories\PurchaseVendorUserNotesFactory::new();

    }
}
