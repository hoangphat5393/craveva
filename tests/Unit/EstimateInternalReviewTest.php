<?php

use App\Models\Company;
use App\Models\Estimate;
use App\Support\EstimatesPhase1Review;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    Schema::dropIfExists('module_settings');
    Schema::create('module_settings', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->string('module_name');
        $table->string('type');
        $table->string('status');
        $table->boolean('is_allowed')->default(false);
        $table->timestamps();
    });

    session()->forget('company');
    Cache::flush();
});

afterEach(function (): void {
    Schema::dropIfExists('module_settings');
    session()->forget('company');
});

function enablePhase1ReviewForUnitTest(): void
{
    $company = new Company;
    $company->id = 1;
    $company->status = 'active';

    DB::table('module_settings')->insert([
        'company_id' => $company->id,
        'module_name' => EstimatesPhase1Review::MODULE_NAME,
        'type' => 'admin',
        'status' => 'active',
        'is_allowed' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Cache::flush();
    session(['company' => $company]);
}

it('treats legacy estimates as ready for conversion', function (): void {
    $estimate = new Estimate;
    $estimate->president_review_status = null;
    $estimate->vp_pricing_review_status = null;

    expect($estimate->hasLegacyInternalReviewState())->toBeTrue();
    expect($estimate->isReadyForCommercialConversion())->toBeTrue();
});

it('requires both president and vp pricing approvals for new review flow', function (): void {
    $estimate = new Estimate;
    $estimate->president_review_status = Estimate::INTERNAL_REVIEW_APPROVED;
    $estimate->vp_pricing_review_status = Estimate::INTERNAL_REVIEW_PENDING;

    expect($estimate->isReadyForCommercialConversion())->toBeFalse();

    $estimate->vp_pricing_review_status = Estimate::INTERNAL_REVIEW_APPROVED;

    expect($estimate->isReadyForCommercialConversion())->toBeTrue();
});

it('builds approval timeline entries in chronological order', function (): void {
    $estimate = new Estimate;
    $estimate->president_review_status = Estimate::INTERNAL_REVIEW_APPROVED;
    $estimate->president_reviewed_at = now()->subHour();
    $estimate->president_review_note = 'Formula OK';
    $estimate->vp_pricing_review_status = Estimate::INTERNAL_REVIEW_APPROVED;
    $estimate->vp_pricing_reviewed_at = now();
    $estimate->vp_pricing_review_note = 'Margin OK';

    $entries = $estimate->approvalTimelineEntries();

    expect($entries)->toHaveCount(2);
    expect($entries[0]['note'])->toBe('Formula OK');
    expect($entries[1]['note'])->toBe('Margin OK');
});

it('marks revision required workflow stage when status is revision_required', function (): void {
    enablePhase1ReviewForUnitTest();

    $estimate = new Estimate;
    $estimate->status = Estimate::STATUS_REVISION_REQUIRED;
    $estimate->president_review_status = Estimate::INTERNAL_REVIEW_REJECTED;
    $estimate->vp_pricing_review_status = Estimate::INTERNAL_REVIEW_PENDING;

    $stage = $estimate->workflowStagePresentation();

    expect($stage['label'])->toBe(__('modules.estimates.workflowStage_revision_required'));
});

it('exposes president and vp pricing reviewer relations', function (): void {
    $estimate = new Estimate;

    expect($estimate->presidentReviewer())->toBeInstanceOf(BelongsTo::class);
    expect($estimate->vpPricingReviewer())->toBeInstanceOf(BelongsTo::class);
});
