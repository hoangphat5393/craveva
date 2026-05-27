@extends('layouts.app')

@php
    use App\Enums\ProductType;

    $formatQuantity = static fn($value): string => rtrim(rtrim(number_format((float) $value, 4, '.', ''), '0'), '.');
    $formatBomCost = static function (?float $value): string {
        if ($value === null) {
            return '—';
        }

        return currency_format($value, company()->currency_id);
    };
    $bomCostSummary = $bomCostSummary ?? ['lines' => [], 'total' => null];
    $bom->loadCount('productionOrders');
    $editable = $bom->production_orders_count === 0;
    $showBomWasteUi = (bool) config('production.ui.show_bom_waste_percent_ui', false);
@endphp

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar flex-wrap">
            <div id="table-actions" class="flex-grow-1 align-items-center mt-3">
                <x-forms.link-secondary :link="route('production.boms.index')" class="mr-3 float-left" icon="arrow-left">
                    @lang('app.back')
                </x-forms.link-secondary>
                @if ($editable && in_array(user()->permission('edit_production_orders'), ['all', 'added', 'owned', 'both'], true))
                    <x-forms.link-primary :link="route('production.boms.edit', $bom)" data-redirect-url="{{ route('production.boms.show', $bom) }}" class="float-left mr-2 openRightModal" icon="pencil-alt">
                        @lang('app.edit')
                    </x-forms.link-primary>
                    <form method="post" action="{{ route('production.boms.destroy', $bom) }}" class="d-inline" onsubmit="return confirm(@json(__('app.areYouSure')));">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger rounded f-14 p-2">
                            <i class="fa fa-trash mr-1"></i>@lang('app.delete')
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success mt-3 mb-0">{{ session('success') }}</div>
        @endif

        @if (!$editable)
            <div class="alert alert-info mt-3 mb-0 f-14">
                @lang('production::app.bomLockedBecauseUsedByOrders')
            </div>
        @endif

        <div class="bg-white rounded p-4 mt-3 mb-4">
            <div class="row f-14">
                <div class="col-md-6 mb-3">
                    <span class="text-dark-grey d-block mb-1">@lang('production::app.manufacturedProduct')</span>
                    <span class="font-weight-normal">{{ $bom->outputProduct?->name ?? '—' }}</span>
                </div>
                <div class="col-md-6 mb-3">
                    <span class="text-dark-grey d-block mb-1">@lang('production::app.bomVersion')</span>
                    <span class="font-weight-normal">{{ $bom->version }}</span>
                </div>
                <div class="col-md-6 mb-3">
                    <span class="text-dark-grey d-block mb-1">@lang('production::app.bomCode')</span>
                    <span class="font-weight-normal">{{ $bom->code ?: '—' }}</span>
                </div>
                <div class="col-md-6 mb-3">
                    <span class="text-dark-grey d-block mb-1">@lang('production::app.bomDefaultForManufacturedProduct')</span>
                    <span class="font-weight-normal">{{ $bom->is_default ? __('app.yes') : __('app.no') }}</span>
                </div>
                <div class="col-md-6 mb-3">
                    <span class="text-dark-grey d-block mb-1">@lang('production::app.bomEffectiveFrom')</span>
                    <span class="font-weight-normal">{{ $bom->effective_from?->format('Y-m-d') ?? '—' }}</span>
                </div>
                <div class="col-md-6 mb-3">
                    <span class="text-dark-grey d-block mb-1">@lang('production::app.bomEffectiveTo')</span>
                    <span class="font-weight-normal">{{ $bom->effective_to?->format('Y-m-d') ?? '—' }}</span>
                </div>
            </div>
            @if ($bom->notes)
                <div class="f-14">
                    <span class="text-dark-grey d-block mb-1">@lang('production::app.bomNotes')</span>
                    <span class="font-weight-normal">{{ $bom->notes }}</span>
                </div>
            @endif
        </div>

        <h5 class="f-14 text-dark-grey font-weight-bold mb-1">@lang('production::app.bomLines')</h5>
        <p class="f-12 text-muted mb-3">@lang('production::app.bomCostingHelp')</p>
        <div class="d-flex flex-column w-tables rounded bg-white table-responsive">
            <table class="table table-hover border-0 w-100 mb-0">
                <thead>
                    <tr>
                        <th class="f-14 text-dark-grey">#</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.componentProduct')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.bomComponentQtyAndUom')</th>
                        @if ($showBomWasteUi)
                            <th class="f-14 text-dark-grey">@lang('production::app.bomWastePercent')</th>
                        @endif
                        <th class="f-14 text-dark-grey text-right">@lang('production::app.bomComponentUnitCost')</th>
                        <th class="f-14 text-dark-grey text-right">@lang('production::app.bomComponentLineTotal')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bom->items as $idx => $line)
                        @php
                            $lineCost = $bomCostSummary['lines'][$idx] ?? ['unit_cost' => null, 'line_total' => null];
                        @endphp
                        <tr>
                            <td class="f-14">{{ $idx + 1 }}</td>
                            <td class="f-14">
                                {{ $line->componentProduct?->name ?? $line->component_product_id }}
                                @if ($line->componentProduct?->type)
                                    <span class="text-dark-grey f-12 d-block">{{ ProductType::labelFor($line->componentProduct->type) }}</span>
                                @endif
                            </td>
                            <td class="f-14">
                                {{ $formatQuantity($line->quantity) }}
                                <span class="text-muted">{{ $line->unit?->unit_type ?? ($line->componentProduct?->unit?->unit_type ?? '') }}</span>
                            </td>
                            @if ($showBomWasteUi)
                                <td class="f-14">{{ rtrim(rtrim(number_format((float) ($line->waste_percent ?? 0), 2, '.', ''), '0'), '.') }}%</td>
                            @endif
                            <td class="f-14 text-right">{{ $formatBomCost($lineCost['unit_cost']) }}</td>
                            <td class="f-14 text-right">{{ $formatBomCost($lineCost['line_total']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $showBomWasteUi ? 6 : 5 }}" class="p-5">
                                <x-cards.no-record icon="cubes" :message="__('messages.noRecordFound')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($bom->items->isNotEmpty())
                    <tfoot>
                        <tr class="bg-additional-grey">
                            <td colspan="{{ $showBomWasteUi ? 5 : 4 }}" class="f-14 text-dark-grey font-weight-bold text-right">@lang('production::app.bomTotalComponentCostPerManufacturedProduct')</td>
                            <td class="f-14 text-right font-weight-bold">{{ $formatBomCost($bomCostSummary['total']) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection
