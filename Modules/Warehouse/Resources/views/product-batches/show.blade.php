@extends('layouts.app')

@php
    $formatQuantity = static fn($value): string => rtrim(rtrim(number_format((float) $value, 4, '.', ''), '0'), '.');
@endphp

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar flex-wrap">
            <div id="table-actions" class="flex-grow-1 align-items-center mt-3">
                <x-forms.link-secondary :link="route('warehouse.product-batches.index')" class="mr-3 float-left" icon="arrow-left">
                    @lang('warehouse::app.backToBatchList')
                </x-forms.link-secondary>
            </div>
        </div>

        <div class="bg-white rounded p-4 mt-3 mb-4">
            <h4 class="f-18 mb-3">@lang('warehouse::app.warehouseBatchDetail') #{{ $batch->id }}</h4>
            <div class="row f-14">
                <div class="col-md-4 mb-2"><strong>@lang('warehouse::app.product')</strong><br>{{ $batch->product?->name ?? '—' }} ({{ $batch->product?->sku ?? '—' }})</div>
                <div class="col-md-4 mb-2"><strong>@lang('warehouse::app.warehouse')</strong><br>{{ $batch->warehouse?->name ?? '—' }}</div>
                <div class="col-md-4 mb-2"><strong>@lang('warehouse::app.batch')</strong><br>{{ $batch->batch_number ?: '—' }}</div>
                <div class="col-md-4 mb-2"><strong>@lang('warehouse::app.quantity')</strong><br>{{ $formatQuantity($batch->quantity) }}</div>
                <div class="col-md-4 mb-2"><strong>@lang('warehouse::app.reservedQuantity')</strong><br>{{ $formatQuantity($batch->reserved_quantity ?? 0) }}</div>
                <div class="col-md-4 mb-2"><strong>@lang('app.expiryDate')</strong><br>
                    @if ($batch->expiration_date)
                        {{ \Illuminate\Support\Carbon::parse($batch->expiration_date)->format(company()->date_format) }}
                    @else
                        —
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded p-4 mt-3">
            <h5 class="f-16 mb-3">@lang('warehouse::app.relatedStockMovements')</h5>
            @if ($movements->isEmpty())
                <p class="text-muted f-13 mb-0">@lang('warehouse::app.noMovementsForBatchIdentity')</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover border-0 w-100 mb-0 f-13">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>@lang('warehouse::app.movementType')</th>
                                <th>@lang('warehouse::app.quantity')</th>
                                <th>@lang('warehouse::app.fromWarehouse')</th>
                                <th>@lang('warehouse::app.toWarehouse')</th>
                                <th>@lang('warehouse::app.reference')</th>
                                <th>@lang('app.action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($movements as $m)
                                @php
                                    $movementLabel = match ($m->movement_type) {
                                        'inbound' => __('warehouse::app.inbound'),
                                        'outbound' => __('warehouse::app.outbound'),
                                        default => $m->movement_type,
                                    };
                                    $referenceBase = $m->reference_type ? (str_contains($m->reference_type, '\\') ? class_basename($m->reference_type) : $m->reference_type) : '';
                                    $referenceKey = strtolower($referenceBase);
                                    $referenceLabel = match ($referenceKey) {
                                        'manual_warehouse_stock' => __('warehouse::app.reference_manual_warehouse_stock'),
                                        'manual_transfer' => __('warehouse::app.reference_manual_transfer'),
                                        'invoice' => __('warehouse::app.reference_invoice'),
                                        'invoice_stock_reversal' => __('warehouse::app.reference_invoice_stock_reversal'),
                                        'creditnotes' => __('warehouse::app.reference_credit_notes'),
                                        'credit_note_stock_reversal' => __('warehouse::app.reference_credit_note_stock_reversal'),
                                        'purchasevendorcredit' => __('warehouse::app.reference_purchase_vendor_credit'),
                                        'purchase_vendor_credit_stock_reversal' => __('warehouse::app.reference_purchase_vendor_credit_stock_reversal'),
                                        'salesshipment' => __('warehouse::app.reference_sales_shipment'),
                                        'sales_shipment_stock_reversal' => __('warehouse::app.reference_sales_shipment_stock_reversal'),
                                        'productionbatch' => __('warehouse::app.reference_production_batch'),
                                        'transfer' => __('warehouse::app.reference_transfer'),
                                        default => $m->reference_type ? \Illuminate\Support\Str::headline(str_replace('_', ' ', $referenceBase)) : '—',
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $m->id }}</td>
                                    <td>{{ $movementLabel }}</td>
                                    <td>{{ $formatQuantity($m->quantity) }}</td>
                                    <td>{{ $m->warehouse_from_id ?? '—' }}</td>
                                    <td>{{ $m->warehouse_to_id ?? '—' }}</td>
                                    <td>
                                        @if ($m->reference_type && $m->reference_id)
                                            {{ $referenceLabel }} #{{ $m->reference_id }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        @if ($m->reference_type === $productionBatchReferenceType && $m->reference_id)
                                            <a href="{{ route('production.batches.trace', $m->reference_id) }}" class="f-13 mr-2">@lang('warehouse::app.openProductionTrace')</a>
                                            <a href="{{ route('production.batches.show', $m->reference_id) }}" class="f-13">@lang('warehouse::app.openProductionBatch')</a>
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
    </div>
@endsection
