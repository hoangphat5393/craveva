<?php

declare(strict_types=1);

namespace App\Services\Estimates;

use App\Models\Estimate;

final class EstimateVpMarginPolicy
{
    public function __construct(
        private readonly EstimateRecipeMarginSummary $marginSummary = new EstimateRecipeMarginSummary,
        private readonly EstimatePhase1CompanySettings $companySettings = new EstimatePhase1CompanySettings,
    ) {}

    public function minimumGrossMarginPercent(?int $companyId = null): ?float
    {
        return $this->companySettings->minimumGrossMarginPercent($companyId);
    }

    /**
     * @return array{allowed: bool, margin_percent: float|null, minimum_percent: float|null}
     */
    public function evaluateForVpApproval(Estimate $estimate): array
    {
        $minimum = $this->minimumGrossMarginPercent((int) $estimate->company_id);

        if ($minimum === null) {
            return ['allowed' => true, 'margin_percent' => null, 'minimum_percent' => null];
        }

        $summary = $this->marginSummary->summarize($estimate);

        if (! $summary['has_bom_lines'] || $summary['gross_margin_percent'] === null) {
            return [
                'allowed' => true,
                'margin_percent' => $summary['gross_margin_percent'],
                'minimum_percent' => $minimum,
            ];
        }

        return [
            'allowed' => $summary['gross_margin_percent'] >= $minimum,
            'margin_percent' => $summary['gross_margin_percent'],
            'minimum_percent' => $minimum,
        ];
    }
}
