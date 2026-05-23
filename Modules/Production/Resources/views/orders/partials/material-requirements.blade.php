@php
    /** @var list<array<string, mixed>> $materialRequirements */
    /** @var float $materialRequirementsPlannedFg */
    /** @var bool $materialRequirementsHasShortfall */

    $formatQty = static function (float $value): string {
        return rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.') ?: '0';
    };

    $showBomWasteUi = (bool) config('production.ui.show_bom_waste_percent_ui', false);
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
        <div class="table-responsive">
            <table class="table table-sm border f-13 mb-0">
                <thead>
                    <tr>
                        <th>@lang('production::app.componentProduct')</th>
                        <th>@lang('production::app.materialQtyPerManufacturedProductUnit')</th>
                        @if ($showBomWasteUi)
                            <th>@lang('production::app.bomWastePercent')</th>
                        @endif
                        <th>@lang('production::app.materialTotalRequired')</th>
                        @if (!empty($materialRequirementsShowStock))
                            <th>@lang('production::app.materialAvailableInRawMaterialWarehouse')</th>
                            <th>@lang('production::app.materialShortfall')</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($materialRequirements as $row)
                        <tr @class([
                            'table-warning' =>
                                ($row['shortfall'] ?? null) !== null && (float) $row['shortfall'] > 0,
                        ])>
                            <td>{{ $row['component_name'] }}</td>
                            <td>
                                {{ $formatQty((float) $row['quantity_per_fg_unit']) }}
                                @if (!empty($row['unit_label']))
                                    <span class="text-muted">{{ $row['unit_label'] }}</span>
                                @endif
                            </td>
                            @if ($showBomWasteUi)
                                <td>{{ $formatQty((float) ($row['waste_percent'] ?? 0)) }}%</td>
                            @endif
                            <td>
                                {{ $formatQty((float) $row['total_required']) }}
                                @if (!empty($row['unit_label_base']))
                                    <span class="text-muted">{{ $row['unit_label_base'] }}</span>
                                @endif
                            </td>
                            @if (!empty($materialRequirementsShowStock))
                                <td>
                                    {{ $formatQty((float) ($row['available_in_rm_warehouse'] ?? 0)) }}
                                    @if (!empty($row['unit_label_base']))
                                        <span class="text-muted">{{ $row['unit_label_base'] }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if (($row['shortfall'] ?? null) !== null && (float) $row['shortfall'] > 0)
                                        <span class="text-danger font-weight-bold">{{ $formatQty((float) $row['shortfall']) }}</span>
                                        @if (!empty($row['unit_label_base']))
                                            <span class="text-muted">{{ $row['unit_label_base'] }}</span>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
