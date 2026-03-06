<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyTimesheetEntries extends BaseModel
{
    use HasCompany;
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function weeklyTimesheet(): BelongsTo
    {
        return $this->belongsTo(WeeklyTimesheet::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
