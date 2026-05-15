@php
    /** @var \App\Models\Estimate $estimate */
@endphp

@if ($estimate->hasLegacyInternalReviewState())
    <x-alert type="secondary" class="mb-3 f-13">
        <strong>@lang('modules.estimates.internalReviewHeading')</strong> — @lang('modules.estimates.internalReviewLegacy')
    </x-alert>
@else
    <div class="card border border-additional-grey mb-3">
        <div class="card-body py-3 px-3">
            <p class="mb-3 f-14 font-weight-bold text-dark">@lang('modules.estimates.internalReviewHeading')</p>
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <p class="mb-1 f-12 text-dark-grey">@lang('modules.estimates.internalReviewPresident')</p>
                    @php
                        $pStatus = $estimate->president_review_status;
                        $pClass = $pStatus === \App\Models\Estimate::INTERNAL_REVIEW_APPROVED ? 'badge-success' : ($pStatus === \App\Models\Estimate::INTERNAL_REVIEW_REJECTED ? 'badge-danger' : 'badge-warning');
                    @endphp
                    <span class="badge {{ $pClass }} f-12">{{ __('modules.estimates.internalReview_' . (in_array((string) $pStatus, ['pending', 'approved', 'rejected'], true) ? $pStatus : 'pending')) }}</span>
                    @if ($estimate->presidentReviewer)
                        <p class="mb-0 mt-2 f-13 text-dark">
                            <span class="text-dark-grey">@lang('modules.estimates.internalReviewReviewedBy'):</span>
                            {{ $estimate->presidentReviewer->name }}
                        </p>
                    @endif
                    @if ($estimate->president_reviewed_at)
                        <p class="mb-0 mt-1 f-12 text-dark-grey">
                            @lang('modules.estimates.internalReviewReviewedAt'):
                            {{ $estimate->president_reviewed_at->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}
                        </p>
                    @endif
                    @if ($estimate->president_review_note)
                        <p class="mb-0 mt-2 f-12 text-dark-grey">{{ $estimate->president_review_note }}</p>
                    @endif
                </div>
                <div class="col-md-6">
                    <p class="mb-1 f-12 text-dark-grey">@lang('modules.estimates.internalReviewVpPricing')</p>
                    @php
                        $vpStatus = $estimate->vp_pricing_review_status;
                        $vpClass = $vpStatus === \App\Models\Estimate::INTERNAL_REVIEW_APPROVED ? 'badge-success' : ($vpStatus === \App\Models\Estimate::INTERNAL_REVIEW_REJECTED ? 'badge-danger' : 'badge-warning');
                    @endphp
                    <span class="badge {{ $vpClass }} f-12">{{ __('modules.estimates.internalReview_' . (in_array((string) $vpStatus, ['pending', 'approved', 'rejected'], true) ? $vpStatus : 'pending')) }}</span>
                    @if ($estimate->vpPricingReviewer)
                        <p class="mb-0 mt-2 f-13 text-dark">
                            <span class="text-dark-grey">@lang('modules.estimates.internalReviewReviewedBy'):</span>
                            {{ $estimate->vpPricingReviewer->name }}
                        </p>
                    @endif
                    @if ($estimate->vp_pricing_reviewed_at)
                        <p class="mb-0 mt-1 f-12 text-dark-grey">
                            @lang('modules.estimates.internalReviewReviewedAt'):
                            {{ $estimate->vp_pricing_reviewed_at->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}
                        </p>
                    @endif
                    @if ($estimate->vp_pricing_review_note)
                        <p class="mb-0 mt-2 f-12 text-dark-grey">{{ $estimate->vp_pricing_review_note }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
