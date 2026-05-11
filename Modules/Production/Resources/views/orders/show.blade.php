@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar flex-wrap">
            <div id="table-actions" class="flex-grow-1 align-items-center mt-3">
                <x-forms.link-secondary :link="route('production.orders.index')" class="mr-3 float-left" icon="arrow-left">
                    @lang('app.back')
                </x-forms.link-secondary>
                @if ($order->status === \Modules\Production\Entities\ProductionOrder::STATUS_DRAFT && in_array(user()->permission('edit_production_orders'), ['all', 'added', 'owned', 'both'], true))
                    <x-forms.link-primary :link="route('production.orders.edit', $order)" class="float-left" icon="pencil-alt">
                        @lang('app.edit')
                    </x-forms.link-primary>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success mt-3 mb-0">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger mt-3 mb-0">{{ session('error') }}</div>
        @endif

        @if ($order->production_bom_id === null)
            <div class="alert alert-warning mt-3 mb-0 f-14">
                @lang('production::app.bomMissingHint')
            </div>
        @endif

        @php
            $plannedQty = (float) $order->planned_quantity;
            $registeredFgTotal = $order->batches->sum(static fn($b) => $b->outputs->sum(static fn($o) => (float) $o->quantity));
            $varianceQty = round($registeredFgTotal - $plannedQty, 4);
            $variancePct = $plannedQty > 0.0000001 ? round((($registeredFgTotal - $plannedQty) / $plannedQty) * 100, 2) : null;
        @endphp

        <div class="bg-white rounded p-4 mt-3 mb-4">
            <div class="row f-14">
                <div class="col-md-6 mb-3">
                    <span class="text-dark-grey d-block mb-1">@lang('production::app.status')</span>
                    <span class="font-weight-normal">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                </div>
                <div class="col-md-6 mb-3">
                    <span class="text-dark-grey d-block mb-1">@lang('production::app.fgProduct')</span>
                    <span class="font-weight-normal">{{ $order->outputProduct?->name ?? '—' }}</span>
                    <span class="text-dark-grey d-block mb-1 mt-2">@lang('modules.invoices.unitType')</span>
                    <span class="font-weight-normal">{{ $orderFgUnitType ?? '—' }}</span>
                </div>
                <div class="col-md-6 mb-3">
                    <span class="text-dark-grey d-block mb-1">@lang('production::app.plannedQty')</span>
                    <span class="font-weight-normal">{{ $order->planned_quantity }}</span>
                </div>
                <div class="col-md-6 mb-3">
                    <span class="text-dark-grey d-block mb-1">@lang('production::app.fgRegisteredTotal')</span>
                    <span class="font-weight-normal">{{ rtrim(rtrim(number_format($registeredFgTotal, 4, '.', ''), '0'), '.') }}</span>
                </div>
                <div class="col-md-6 mb-3">
                    <span class="text-dark-grey d-block mb-1">@lang('production::app.fgVarianceVsPlanned')</span>
                    <span class="font-weight-normal">
                        {{ rtrim(rtrim(number_format($varianceQty, 4, '.', ''), '0'), '.') }}
                        @if ($variancePct !== null)
                            ({{ rtrim(rtrim(number_format((float) $variancePct, 2, '.', ''), '0'), '.') }}%)
                        @endif
                    </span>
                </div>
                <div class="col-md-6 mb-3">
                    <span class="text-dark-grey d-block mb-1">@lang('production::app.rawMaterialWarehouse')</span>
                    <span class="font-weight-normal">{{ $order->rmWarehouse?->name ?? '#' . $order->rm_warehouse_id }}</span>
                </div>
                <div class="col-md-6 mb-3">
                    <span class="text-dark-grey d-block mb-1">@lang('production::app.finishedGoodsWarehouse')</span>
                    <span class="font-weight-normal">{{ $order->fgWarehouse?->name ?? '#' . $order->fg_warehouse_id }}</span>
                </div>
                <div class="col-md-6 mb-3">
                    <span class="text-dark-grey d-block mb-1">@lang('production::app.linkedSalesOrder')</span>
                    <span class="font-weight-normal">
                        @if ($order->sales_order_id)
                            #{{ $order->sales_order_id }} @if ($order->salesOrder)
                                ({{ $order->salesOrder->order_number }})
                            @endif
                        @else
                            —
                        @endif
                    </span>
                </div>
                <div class="col-md-6 mb-3">
                    <span class="text-dark-grey d-block mb-1">@lang('production::app.linkedProject')</span>
                    <span class="font-weight-normal">
                        @if ($order->project_id)
                            {{ $order->project?->project_name ?? '#' . $order->project_id }}
                        @else
                            —
                        @endif
                    </span>
                </div>
                @if ($order->bom_snapshot_at)
                    <div class="col-12 mb-2">
                        <span class="text-dark-grey d-block mb-1">@lang('production::app.bomSnapshotTitle')</span>
                        <p class="f-13 text-muted mb-1">
                            @lang('production::app.bomSnapshotCapturedAt'): {{ $order->bom_snapshot_at }}
                            · @lang('production::app.bomSnapshotPlannedFgQty'): {{ $order->bom_snapshot_planned_quantity }}
                        </p>
                        <table class="table table-sm border f-13 mb-0">
                            <thead>
                                <tr>
                                    <th>@lang('production::app.componentProduct')</th>
                                    <th>@lang('production::app.bomComponentQtyFrozen')</th>
                                    <th>@lang('production::app.bomComponentQtyShadow')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->bomSnapshotItems as $snap)
                                    <tr>
                                        <td>{{ $snap->componentProduct?->name ?? $snap->component_product_id }}</td>
                                        <td>{{ $snap->quantity_per_fg_unit }}</td>
                                        <td>
                                            @if ($snap->quantity_per_fg_unit_base_shadow !== null)
                                                {{ rtrim(rtrim(number_format((float) $snap->quantity_per_fg_unit_base_shadow, 6, '.', ''), '0'), '.') }}
                                                <span class="text-muted">(@lang('production::app.shadowModeLabel'))</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            @if (in_array(user()->permission('edit_production_orders'), ['all', 'added', 'owned', 'both'], true))
                <div class="w-100 border-top-grey pt-3 mt-2 d-flex flex-wrap">
                    @if ($order->status === \Modules\Production\Entities\ProductionOrder::STATUS_DRAFT)
                        <form method="post" action="{{ route('production.orders.release', $order) }}" class="d-inline mr-2 mb-2" onsubmit="return confirm(@json(__('app.areYouSure')));">
                            @csrf
                            <button type="submit" class="btn btn-primary rounded f-14 p-2">
                                <i class="fa fa-paper-plane mr-1"></i>@lang('production::app.release')
                            </button>
                        </form>
                    @endif
                    @if (in_array($order->status, [\Modules\Production\Entities\ProductionOrder::STATUS_DRAFT, \Modules\Production\Entities\ProductionOrder::STATUS_RELEASED], true))
                        <form method="post" action="{{ route('production.orders.cancel', $order) }}" class="d-inline mb-2" onsubmit="return confirm(@json(__('app.areYouSure')));">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger rounded f-14 p-2">
                                <i class="fa fa-ban mr-1"></i>@lang('production::app.cancel')
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>

        @if (in_array($order->status, [\Modules\Production\Entities\ProductionOrder::STATUS_RELEASED, \Modules\Production\Entities\ProductionOrder::STATUS_IN_PROGRESS], true) && $registeredFgTotal <= 0)
            <div class="alert alert-info mt-3 mb-4 f-14">
                <strong>@lang('production::app.inventoryFlowHintTitle')</strong><br>
                @lang('production::app.inventoryFlowHintBody')
            </div>
        @endif

        @if ($registeredFgTotal > 0)
            <div class="alert alert-success mt-3 mb-4 f-14">
                <strong>@lang('production::app.inventoryAggregationHintTitle')</strong><br>
                @lang('production::app.inventoryAggregationHintBody')
            </div>
        @endif

        <h5 class="f-14 text-dark-grey font-weight-bold mb-3">@lang('production::app.batchCode')</h5>
        <div class="d-flex flex-column w-tables rounded bg-white table-responsive">
            <table class="table table-hover border-0 w-100 mb-0">
                <thead>
                    <tr>
                        <th class="f-14 text-dark-grey">@lang('production::app.batchCode')</th>
                        <th class="f-14 text-dark-grey text-right">@lang('app.action')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($order->batches as $batch)
                        <tr>
                            <td>{{ $batch->batch_code }}</td>
                            <td class="text-right">
                                <a href="{{ route('production.batches.show', $batch) }}" class="btn btn-secondary rounded f-14 btn-sm">
                                    <i class="fa fa-eye mr-1"></i>@lang('production::app.viewBatch')
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="p-5">
                                <x-cards.no-record icon="cubes" :message="__('messages.noRecordFound')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
