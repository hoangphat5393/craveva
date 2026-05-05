@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar flex-wrap">
            <div id="table-actions" class="flex-grow-1 align-items-center mt-3">
                <x-forms.link-secondary :link="route('production.batches.show', $batch)" class="float-left" icon="arrow-left">
                    @lang('app.back')
                </x-forms.link-secondary>
            </div>
        </div>

        <h5 class="f-14 text-dark-grey font-weight-bold mb-3 mt-3">@lang('production::app.consumptions') → @lang('production::app.warehouseBatchId') (RM)</h5>
        <div class="d-flex flex-column w-tables rounded mb-4 bg-white table-responsive">
            <table class="table table-hover border-0 w-100 mb-0">
                <thead>
                    <tr>
                        <th class="f-14 text-dark-grey">@lang('production::app.componentProduct')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.warehouseBatchId')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.plannedConsumption')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($batch->consumptions as $line)
                        <tr>
                            <td>{{ $line->componentProduct?->name ?? $line->component_product_id }}</td>
                            <td>{{ $line->warehouse_product_batch_id }} @if ($line->warehouseProductBatch)
                                    ({{ $line->warehouseProductBatch->batch_number ?? '—' }})
                                @endif
                            </td>
                            <td>{{ $line->actual_quantity ?? $line->planned_quantity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <h5 class="f-14 text-dark-grey font-weight-bold mb-3">@lang('warehouse::app.stockMovements') — @lang('production::app.outboundMovements') (RM)</h5>
        <div class="d-flex flex-column w-tables rounded mb-4 bg-white table-responsive">
            <table class="table table-hover border-0 w-100 mb-0">
                <thead>
                    <tr>
                        <th class="f-14 text-dark-grey">@lang('production::app.movementId')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.productId')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.movementQuantity')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.fgBatchNumber')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.referenceType')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($outboundMovements as $m)
                        <tr>
                            <td>{{ $m->id }}</td>
                            <td>{{ $m->product_id }}</td>
                            <td>{{ $m->quantity }}</td>
                            <td>{{ $m->batch_number }}</td>
                            <td><code class="small">{{ $m->reference_type }}</code></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-5">
                                <x-cards.no-record icon="list" :message="__('messages.noRecordFound')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <h5 class="f-14 text-dark-grey font-weight-bold mb-3">@lang('warehouse::app.stockMovements') — @lang('production::app.inboundMovements') (FG)</h5>
        <div class="d-flex flex-column w-tables rounded bg-white table-responsive">
            <table class="table table-hover border-0 w-100 mb-0">
                <thead>
                    <tr>
                        <th class="f-14 text-dark-grey">@lang('production::app.movementId')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.productId')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.movementQuantity')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.fgBatchNumber')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.referenceType')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inboundMovements as $m)
                        <tr>
                            <td>{{ $m->id }}</td>
                            <td>{{ $m->product_id }}</td>
                            <td>{{ $m->quantity }}</td>
                            <td>{{ $m->batch_number }}</td>
                            <td><code class="small">{{ $m->reference_type }}</code></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-5">
                                <x-cards.no-record icon="list" :message="__('messages.noRecordFound')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
