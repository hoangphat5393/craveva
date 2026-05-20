<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Estimate;
use App\Models\ModuleSetting;
use App\Scopes\CompanyScope;
use App\Support\EstimatesPhase1Review;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;

uses(DatabaseTransactions::class);

function phase1GateCompany(): ?Company
{
    $company = Company::withoutGlobalScopes()->where('status', 'active')->orderBy('id')->first();

    return $company instanceof Company ? $company : null;
}

function phase1GateEstimate(Company $company): ?Estimate
{
    $estimate = Estimate::withoutGlobalScopes()
        ->where('company_id', $company->id)
        ->orderByDesc('id')
        ->first();

    return $estimate instanceof Estimate ? $estimate : null;
}

it('treats commercial conversion as allowed when phase1 review module is disabled for tenant', function () {
    $company = phase1GateCompany();
    if ($company === null) {
        test()->markTestSkipped('No active company.');
    }

    $estimate = phase1GateEstimate($company);
    if ($estimate === null) {
        test()->markTestSkipped('No estimate for tenant.');
    }

    ModuleSetting::withoutGlobalScope(CompanyScope::class)->updateOrCreate(
        [
            'company_id' => $company->id,
            'module_name' => 'estimates_phase1_review',
            'type' => 'admin',
        ],
        [
            'is_allowed' => 0,
            'status' => 'deactive',
        ],
    );

    Cache::flush();

    session(['company' => $company]);

    expect(EstimatesPhase1Review::enabled())->toBeFalse();

    $estimate->forceFill([
        'president_review_status' => Estimate::INTERNAL_REVIEW_PENDING,
        'vp_pricing_review_status' => Estimate::INTERNAL_REVIEW_PENDING,
    ])->save();

    expect($estimate->fresh()->isCommercialConversionAllowed())->toBeTrue();
});

it('blocks commercial conversion when phase1 is enabled and reviews are pending', function () {
    $company = phase1GateCompany();
    if ($company === null) {
        test()->markTestSkipped('No active company.');
    }

    $estimate = phase1GateEstimate($company);
    if ($estimate === null) {
        test()->markTestSkipped('No estimate for tenant.');
    }

    ModuleSetting::withoutGlobalScope(CompanyScope::class)->updateOrCreate(
        [
            'company_id' => $company->id,
            'module_name' => 'estimates_phase1_review',
            'type' => 'admin',
        ],
        [
            'is_allowed' => 1,
            'status' => 'active',
        ],
    );

    Cache::flush();

    session(['company' => $company]);

    expect(EstimatesPhase1Review::enabled())->toBeTrue();

    $estimate->forceFill([
        'president_review_status' => Estimate::INTERNAL_REVIEW_PENDING,
        'vp_pricing_review_status' => Estimate::INTERNAL_REVIEW_PENDING,
    ])->save();

    expect($estimate->fresh()->isCommercialConversionAllowed())->toBeFalse();
});

it('returns workflow stage label for pending president when phase1 is enabled', function () {
    $company = phase1GateCompany();
    if ($company === null) {
        test()->markTestSkipped('No active company.');
    }

    $estimate = phase1GateEstimate($company);
    if ($estimate === null) {
        test()->markTestSkipped('No estimate for tenant.');
    }

    ModuleSetting::withoutGlobalScope(CompanyScope::class)->updateOrCreate(
        [
            'company_id' => $company->id,
            'module_name' => 'estimates_phase1_review',
            'type' => 'admin',
        ],
        [
            'is_allowed' => 1,
            'status' => 'active',
        ],
    );

    Cache::flush();

    session(['company' => $company]);

    $estimate->forceFill([
        'president_review_status' => Estimate::INTERNAL_REVIEW_PENDING,
        'vp_pricing_review_status' => Estimate::INTERNAL_REVIEW_PENDING,
    ])->save();

    expect($estimate->fresh()->workflowStagePresentation()['label'])->toBe(__('modules.estimates.workflowStage_pending_president'));
});
