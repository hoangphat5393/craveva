@php
    /** @var list<array<string, mixed>> $materialRequirements */
    /** @var float $materialRequirementsPlannedFg */
    /** @var bool $materialRequirementsHasShortfall */

    $formatQty = static function (float $value): string {
        return rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.') ?: '0';
    };
@endphp

@if (count($materialRequirements) > 0)
    <div class="bg-white rounded p-4 mt-3 mb-4 border">
        <h5 class="f-14 text-dark-grey font-weight-bold mb-2">@lang('production::app.materialRequirementsTitle')</h5>
        <p class="f-13 text-muted mb-3">
            @lang('production::app.materialRequirementsHelp', ['qty' => $formatQty($materialRequirementsPlannedFg)])
        </p>
        @if ($materialRequirementsHasShortfall)
            <div class="alert alert-warning f-13 mb-3">
                @lang('production::app.materialRequirementsShortfallWarning')
                @if (!empty($purchaseOrderCreateUrl))
                    <a href="{{ $purchaseOrderCreateUrl }}" class="alert-link openRightModal ml-1">@lang('production::app.suggestPurchaseOrder')</a>
                @endif
            </div>
        @endif
        @include('production::orders.partials.material-requirements-table', [
            'materialRequirements' => $materialRequirements,
            'materialRequirementsShowStock' => $materialRequirementsShowStock ?? false,
        ])
    </div>
@endif
