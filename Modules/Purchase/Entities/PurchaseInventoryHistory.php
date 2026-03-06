<?php

namespace Modules\Purchase\Entities;

use App\Models\BaseModel;
use App\Models\User;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseInventoryHistory extends BaseModel
{
    use HasCompany, HasFactory;

    protected $fillable = [];

    protected static function newFactory()
    {
        return \Modules\Purchase\Database\factories\PurchaseInventoryHistoryFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function inventoryFiles(): BelongsTo
    {
        return $this->belongsTo(PurchaseInventoryFile::class, 'purchase_inventory_files_id');
    }
}
