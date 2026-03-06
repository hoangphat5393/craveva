<?php

namespace Modules\Purchase\Entities;

use App\Models\BaseModel;
use App\Models\User;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseVendorNote extends BaseModel
{
    use HasCompany;

    protected $fillable = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function members(): HasMany
    {
        return $this->hasMany(PurchaseVendorUserNotes::class, 'vendor_note_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchase_vendor_id');
    }
}
