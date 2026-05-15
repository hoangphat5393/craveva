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

        <h5 class="f-14 text-dark-grey font-weight-bold mb-3 mt-3">@lang('production::app.rawMaterialsUsedTraceHeading')</h5>
        <div class="d-flex flex-column w-tables rounded mb-4 bg-white table-responsive">
            <table class="table table-hover border-0 w-100 mb-0">
                <thead>
                    <tr>
                        <th class="f-14 text-dark-grey">@lang('production::app.rawMaterialProduct')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.rawMaterialBatchId')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.plannedQuantityLine')</th>
                        @if (!empty($canLinkWarehouseBatches))
                            <th class="f-14 text-dark-grey">@lang('production::app.inventoryBatchColumn')</th>
                        @endif
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
                            @if (!empty($canLinkWarehouseBatches))
                                <td class="f-13">
                                    @if ($line->warehouse_product_batch_id)
                                        <a href="{{ route('warehouse.product-batches.show', $line->warehouse_product_batch_id) }}">@lang('production::app.openWarehouseBatch')</a>
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

        <h5 class="f-14 text-dark-grey font-weight-bold mb-3">@lang('warehouse::app.stockMovements') — @lang('production::app.outboundRawMaterialMovements')</h5>
        <div class="d-flex flex-column w-tables rounded mb-4 bg-white table-responsive">
            <table class="table table-hover border-0 w-100 mb-0">
                <thead>
                    <tr>
                        <th class="f-14 text-dark-grey">@lang('production::app.movementId')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.productId')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.movementQuantity')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.fgBatchNumber')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.referenceType')</th>
                        @if (!empty($canLinkWarehouseBatches))
                            <th class="f-14 text-dark-grey">@lang('production::app.inventoryBatchColumn')</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($outboundMovements as $m)
                        @php
                            $ref = $m->reference_type;
                            $refRaw = $ref ? (str_contains($ref, '\\') ? class_basename($ref) : $ref) : '';
                            $refLabel = $refRaw ? \Illuminate\Support\Str::headline(str_replace('_', ' ', $refRaw)) : '—';
                        @endphp
                        <tr>
                            <td>{{ $m->id }}</td>
                            <td>{{ $m->product_id }}</td>
                            <td>{{ $m->quantity }}</td>
                            <td>{{ $m->batch_number }}</td>
                            <td>{{ $refLabel }}</td>
                            @if (!empty($canLinkWarehouseBatches))
                                <td class="f-13">
                                    @php
                                        $wbOut = $outboundWarehouseBatchIds[$m->id] ?? null;
                                    @endphp
                                    @if ($wbOut)
                                        <a href="{{ route('warehouse.product-batches.show', $wbOut) }}">@lang('production::app.openWarehouseBatch')</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ !empty($canLinkWarehouseBatches) ? 6 : 5 }}" class="p-5">
                                <x-cards.no-record icon="list" :message="__('messages.noRecordFound')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <h5 class="f-14 text-dark-grey font-weight-bold mb-3">@lang('warehouse::app.stockMovements') — @lang('production::app.inboundFinishedGoodsMovements')</h5>
        <div class="d-flex flex-column w-tables rounded bg-white table-responsive">
            <table class="table table-hover border-0 w-100 mb-0">
                <thead>
                    <tr>
                        <th class="f-14 text-dark-grey">@lang('production::app.movementId')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.productId')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.movementQuantity')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.fgBatchNumber')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.referenceType')</th>
                        @if (!empty($canLinkWarehouseBatches))
                            <th class="f-14 text-dark-grey">@lang('production::app.inventoryBatchColumn')</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inboundMovements as $m)
                        @php
                            $ref = $m->reference_type;
                            $refRaw = $ref ? (str_contains($ref, '\\') ? class_basename($ref) : $ref) : '';
                            $refLabel = $refRaw ? \Illuminate\Support\Str::headline(str_replace('_', ' ', $refRaw)) : '—';
                        @endphp
                        <tr>
                            <td>{{ $m->id }}</td>
                            <td>{{ $m->product_id }}</td>
                            <td>{{ $m->quantity }}</td>
                            <td>{{ $m->batch_number }}</td>
                            <td>{{ $refLabel }}</td>
                            @if (!empty($canLinkWarehouseBatches))
                                <td class="f-13">
                                    @php
                                        $wbIn = $inboundWarehouseBatchIds[$m->id] ?? null;
                                    @endphp
                                    @if ($wbIn)
                                        <a href="{{ route('warehouse.product-batches.show', $wbIn) }}">@lang('production::app.openWarehouseBatch')</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ !empty($canLinkWarehouseBatches) ? 6 : 5 }}" class="p-5">
                                <x-cards.no-record icon="list" :message="__('messages.noRecordFound')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
