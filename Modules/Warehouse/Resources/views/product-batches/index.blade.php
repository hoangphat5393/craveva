@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
    <style>
        .content-wrapper .pagination .page-link svg {
            width: 14px !important;
            height: 14px !important;
        }
    </style>
@endpush

@php
    $formatQuantity = static fn($value): string => rtrim(rtrim(number_format((float) $value, 4, '.', ''), '0'), '.');
    $warehousePerPage = in_array((int) ($warehousePerPage ?? request('per_page', 25)), [10, 25, 50, 100], true) ? (int) ($warehousePerPage ?? request('per_page', 25)) : 25;
@endphp

@section('filter-section')
    <form method="GET" action="{{ route('warehouse.product-batches.index') }}" id="warehouse-product-batches-filter">
        <x-filters.filter-box>
            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('warehouse::app.warehouse')</p>
                <div class="select-status">
                    <select class="form-control select-picker" name="warehouse_id" data-container="body" data-size="8">
                        <option value="">@lang('warehouse::app.allWarehouses')</option>
                        @foreach ($warehouses as $w)
                            <option value="{{ $w->id }}" @selected((string) $warehouseId === (string) $w->id)>
                                {{ $w->name }}{{ $w->code ? ' (' . $w->code . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="task-search d-flex py-1 px-lg-3 px-0 border-right-grey align-items-center">
                <div class="input-group bg-grey rounded w-100">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" name="search" class="form-control f-14 p-1 border-additional-grey" placeholder="@lang('app.startTyping')" value="{{ request('search') }}">
                </div>
            </div>
            <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
                <button type="submit" class="btn btn-secondary rounded f-14 p-2">
                    <i class="fa fa-search mr-1"></i> @lang('app.apply')
                </button>
            </div>
        </x-filters.filter-box>
    </form>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center mt-3">
                <x-forms.link-secondary :link="route('warehouse.stock.index')" class="mr-3 float-left" icon="arrow-left">
                    @lang('warehouse::app.backToWarehouseStock')
                </x-forms.link-secondary>
            </div>
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            <table class="table table-hover border-0 w-100 mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>@lang('warehouse::app.batch')</th>
                        <th>@lang('warehouse::app.product')</th>
                        <th>@lang('warehouse::app.warehouse')</th>
                        <th>@lang('warehouse::app.quantity')</th>
                        <th>@lang('warehouse::app.reservedQuantity')</th>
                        <th>@lang('app.action')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $row)
                        <tr>
                            <td>{{ $loop->iteration + ($batches->currentPage() - 1) * $batches->perPage() }}</td>
                            <td>
                                <span class="font-weight-semibold">#{{ $row->id }}</span>
                                @if ($row->batch_number)
                                    <br><small class="text-muted">{{ $row->batch_number }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $row->product?->name ?? '—' }}
                                <br><small class="text-lightest">{{ $row->product?->sku ?? '—' }}</small>
                            </td>
                            <td>{{ $row->warehouse?->name ?? '—' }}</td>
                            <td class="font-weight-semibold">{{ $formatQuantity($row->quantity) }}</td>
                            <td>{{ $formatQuantity($row->reserved_quantity ?? 0) }}</td>
                            <td>
                                <a href="{{ route('warehouse.product-batches.show', $row) }}" class="btn btn-sm btn-secondary rounded f-13">
                                    <i class="fa fa-eye mr-1"></i>@lang('app.view')
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-4 text-center text-dark-grey f-13">
                                @lang('warehouse::app.noWarehouseBatches')
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($batches->hasPages())
            <div class="mt-3">
                {{ $batches->appends(request()->except('page'))->links() }}
            </div>
        @endif
    </div>
@endsection
