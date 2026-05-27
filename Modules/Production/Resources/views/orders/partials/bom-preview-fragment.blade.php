@php
    /** @var list<array<string, mixed>> $materialRequirements */
    /** @var float $materialRequirementsPlannedFg */
    /** @var bool $materialRequirementsHasShortfall */

    $formatQty = static function (float $value): string {
        return rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.') ?: '0';
    };
@endphp

@if (count($materialRequirements) === 0)
    <div class="alert alert-warning f-13 mb-0">@lang('production::app.bomHasNoLines')</div>
@else
    <h5 class="f-14 text-dark-grey font-weight-bold mb-2">@lang('production::app.materialRequirementsTitle')</h5>
    <p class="f-13 text-muted mb-3">
        @lang('production::app.materialRequirementsHelp', ['qty' => $formatQty($materialRequirementsPlannedFg)])
    </p>
    <p class="f-12 text-muted mb-2">@lang('production::app.bomPreviewReadOnlyHint')</p>
    @if ($materialRequirementsHasShortfall)
        <div class="alert alert-warning f-13 mb-3">
            @lang('production::app.materialRequirementsShortfallWarning')
        </div>
    @endif
    @include('production::orders.partials.material-requirements-table', [
        'materialRequirements' => $materialRequirements,
        'materialRequirementsShowStock' => $materialRequirementsShowStock ?? false,
    ])
@endif
