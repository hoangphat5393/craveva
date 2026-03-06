<?php

namespace Modules\Purchase\Entities;

use App\Models\BankAccount;
use App\Models\BaseModel;
use App\Models\User;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseVendorPayment extends BaseModel
{
    use HasCompany;

    protected $fillable = [];

    protected $casts = [
        'payment_date' => 'datetime',
    ];

    protected $with = [];

    /**
     * Get all of the comments for the PurchaseVendorPayment
     */
    public function vendor(): HasOne
    {
        return $this->HasOne(PurchaseVendor::class, 'id', 'purchase_vendor_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
