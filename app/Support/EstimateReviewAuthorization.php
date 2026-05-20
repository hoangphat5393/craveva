<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Estimate;

final class EstimateReviewAuthorization
{
    public static function canApprovePresident(): bool
    {
        return self::hasApproverPermission('approve_estimate_president');
    }

    public static function canApproveVpPricing(): bool
    {
        return self::hasApproverPermission('approve_estimate_vp_pricing');
    }

    public static function canSubmitForReview(Estimate $estimate): bool
    {
        if (! estimates_phase1_review_enabled()) {
            return false;
        }

        if (! in_array($estimate->status, ['draft', 'waiting', Estimate::STATUS_REVISION_REQUIRED], true)) {
            return false;
        }

        if ($estimate->hasLegacyInternalReviewState()) {
            return true;
        }

        return ! $estimate->isReadyForCommercialConversion();
    }

    private static function hasApproverPermission(string $permissionName): bool
    {
        return in_array(user()->permission($permissionName), ['all', 'added'], true);
    }
}
