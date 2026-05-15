<?php

use App\Models\Estimate;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

it('exposes president and vp pricing reviewer relations', function (): void {
    $estimate = new Estimate;

    expect($estimate->presidentReviewer())->toBeInstanceOf(BelongsTo::class);
    expect($estimate->vpPricingReviewer())->toBeInstanceOf(BelongsTo::class);
});
