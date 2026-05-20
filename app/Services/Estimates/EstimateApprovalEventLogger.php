<?php

declare(strict_types=1);

namespace App\Services\Estimates;

use App\Models\Estimate;
use App\Models\EstimateApprovalEvent;

final class EstimateApprovalEventLogger
{
    public const EVENT_SUBMITTED = 'submitted';

    public const EVENT_PRESIDENT_APPROVED = 'president_approved';

    public const EVENT_PRESIDENT_REJECTED = 'president_rejected';

    public const EVENT_VP_APPROVED = 'vp_approved';

    public const EVENT_VP_REJECTED = 'vp_rejected';

    public function log(Estimate $estimate, string $eventType, ?string $note = null): void
    {
        if (! estimates_phase1_review_enabled()) {
            return;
        }

        EstimateApprovalEvent::query()->create([
            'company_id' => $estimate->company_id,
            'estimate_id' => $estimate->id,
            'event_type' => $eventType,
            'actor_user_id' => user()?->id,
            'note' => $note !== null && $note !== '' ? $note : null,
        ]);
    }
}
