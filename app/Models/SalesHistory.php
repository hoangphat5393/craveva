<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesHistory extends Model
{
    use HasCompany;

    protected $fillable = [
        'company_id',
        'source_filename',
        'imported_by',
        'imported_at',
        'notes',
    ];

    protected $casts = [
        'imported_at' => 'datetime',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(SalesHistoryLine::class, 'sales_history_id');
    }

    public function importedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
