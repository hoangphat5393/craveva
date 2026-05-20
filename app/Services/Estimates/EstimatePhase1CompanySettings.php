<?php

declare(strict_types=1);

namespace App\Services\Estimates;

use App\Models\InvoiceSetting;

final class EstimatePhase1CompanySettings
{
    public function minimumGrossMarginPercent(?int $companyId = null): ?float
    {
        $companyId = $companyId ?? (int) (company()?->id ?? 0);

        if ($companyId > 0) {
            $invoiceSetting = InvoiceSetting::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->first();

            if ($invoiceSetting !== null && $invoiceSetting->phase1_min_gross_margin_percent !== null) {
                return max(0.0, (float) $invoiceSetting->phase1_min_gross_margin_percent);
            }
        }

        $value = config('estimates_phase1.minimum_gross_margin_percent');

        if ($value === null || $value === '') {
            return null;
        }

        return max(0.0, (float) $value);
    }
}
