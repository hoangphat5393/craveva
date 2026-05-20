@php
    /** @var \App\Models\Estimate $estimate */
    $entries = $estimate->approvalTimelineEntries();
@endphp

@if (estimates_phase1_review_enabled() && !$estimate->hasLegacyInternalReviewState())
    <div class="card border border-additional-grey mb-3">
        <div class="card-body py-3 px-3">
            <p class="mb-2 f-14 font-weight-bold text-dark">@lang('modules.estimates.approvalTimelineHeading')</p>
            @if (count($entries) === 0)
                <p class="mb-0 f-12 text-dark-grey">@lang('modules.estimates.approvalTimelineEmpty')</p>
            @else
                <ul class="list-unstyled mb-0 pl-2 border-left" style="border-color: #e8eef3 !important;">
                    @foreach ($entries as $entry)
                        <li class="mb-3 ml-3 position-relative">
                            <span class="d-inline-block rounded-circle bg-primary position-absolute" style="width: 8px; height: 8px; left: -17px; top: 6px;"></span>
                            <p class="mb-0 f-13 text-dark font-weight-bold">{{ $entry['label'] }}</p>
                            <p class="mb-0 f-12 text-dark-grey">
                                {{ $entry['at']->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}
                                @if (!empty($entry['by']))
                                    · {{ $entry['by'] }}
                                @endif
                            </p>
                            @if (!empty($entry['note']))
                                <p class="mb-0 mt-1 f-12 text-dark-grey">{{ $entry['note'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endif
