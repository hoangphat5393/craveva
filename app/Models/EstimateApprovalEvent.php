<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class EstimateApprovalEvent extends BaseModel
{
    use HasCompany;

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class, 'estimate_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id')->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * @return array{at: Carbon, label: string, by: string|null, note: string|null}
     */
    public function toTimelineEntry(): array
    {
        $key = 'modules.estimates.timelineEvent_'.$this->event_type;

        return [
            'at' => $this->created_at ?? now(),
            'label' => __($key),
            'by' => $this->actor?->name,
            'note' => $this->note,
        ];
    }
}
