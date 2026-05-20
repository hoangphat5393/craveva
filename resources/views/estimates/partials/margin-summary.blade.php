@php
    /** @var \App\Models\Estimate $estimate */
    $summary = app(\App\Services\Estimates\EstimateRecipeMarginSummary::class)->summarize($estimate);
    $minimumMargin = app(\App\Services\Estimates\EstimateVpMarginPolicy::class)->minimumGrossMarginPercent();
@endphp
@if ($summary['has_bom_lines'])
    <div class="card border border-additional-grey mb-3">
        <div class="card-body py-3 px-3">
            <p class="mb-3 f-14 font-weight-bold text-dark">@lang('modules.estimates.marginSummaryHeading')</p>
            <p class="f-12 text-lightest mb-3">@lang('modules.estimates.marginSummaryHelp')</p>
            @if ($minimumMargin !== null)
                <p class="f-12 text-dark-grey mb-3">@lang('modules.estimates.marginMinimumVpRule', ['percent' => number_format($minimumMargin, 2)])</p>
            @endif
            <div class="row f-13">
                <div class="col-md-4 col-sm-6 mb-2">
                    <span class="text-dark-grey">@lang('modules.estimates.marginUnitBomCost')</span><br>
                    <span class="text-dark font-weight-semibold">{{ currency_format($summary['unit_bom_cost'], $estimate->currency_id, false) }}</span>
                </div>
                <div class="col-md-4 col-sm-6 mb-2">
                    <span class="text-dark-grey">@lang('modules.estimates.marginOrderQuantity')</span><br>
                    <span class="text-dark font-weight-semibold">{{ number_format($summary['order_quantity'], 0, '.', ',') }}</span>
                </div>
                <div class="col-md-4 col-sm-6 mb-2">
                    <span class="text-dark-grey">@lang('modules.estimates.marginExtendedBomCost')</span><br>
                    <span class="text-dark font-weight-semibold">{{ currency_format($summary['extended_bom_cost'], $estimate->currency_id, false) }}</span>
                </div>
                <div class="col-md-4 col-sm-6 mb-2">
                    <span class="text-dark-grey">@lang('modules.estimates.marginCommercialSubtotal')</span><br>
                    <span class="text-dark font-weight-semibold">{{ currency_format($summary['commercial_sub_total'], $estimate->currency_id, false) }}</span>
                </div>
                @if ($summary['gross_margin_percent'] !== null)
                    <div class="col-md-4 col-sm-6 mb-2">
                        <span class="text-dark-grey">@lang('modules.estimates.marginGrossPercent')</span><br>
                        @php
                            $warnThreshold = $minimumMargin ?? 15;
                            $marginClass = $summary['gross_margin_percent'] < 0 ? 'text-danger' : ($summary['gross_margin_percent'] < $warnThreshold ? 'text-warning' : 'text-success');
                        @endphp
                        <span class="font-weight-semibold {{ $marginClass }}">{{ $summary['gross_margin_percent'] }}%</span>
                        <span class="text-dark-grey f-12"> ({{ currency_format((float) $summary['gross_margin_amount'], $estimate->currency_id, false) }})</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
