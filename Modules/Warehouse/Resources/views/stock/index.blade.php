@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@php
    $addStockPerm = user()->permission('add_warehouse_stock');
    $transferPerm = user()->permission('manage_warehouse_transfer');
@endphp

@section('filter-section')
    <form method="GET" action="{{ route('warehouse.stock.index') }}" id="warehouse-stock-filter">
        <x-filters.filter-box>
            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('warehouse::app.warehouse')</p>
                <div class="select-status">
                    <select class="form-control select-picker" name="warehouse_id" id="warehouse-stock-warehouse" data-container="body" data-size="8">
                        <option value="">@lang('warehouse::app.allWarehouses')</option>
                        @foreach ($warehouses as $w)
                            <option value="{{ $w->id }}" @selected((string) $warehouseId === (string) $w->id)>{{ $w->name }}</option>
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
                    <input type="text" name="search" class="form-control f-14 p-1 border-additional-grey" id="warehouse-stock-search" placeholder="@lang('app.startTyping')" value="{{ request('search') }}">
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
                @if ($addStockPerm == 'all' || $addStockPerm == 'added')
                    <x-forms.link-primary :link="route('warehouse.stock.create')" class="mr-3 float-left" icon="plus">
                        @lang('warehouse::app.addStock')
                    </x-forms.link-primary>
                @endif
                @if ($transferPerm == 'all' || $transferPerm == 'added')
                    <x-forms.link-secondary :link="route('warehouse.transfer.create')" class="mr-3 float-left" icon="exchange-alt">
                        @lang('warehouse::app.transferStock')
                    </x-forms.link-secondary>
                @endif
            </div>
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            <table class="table table-hover border-0 w-100 mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>@lang('warehouse::app.product')</th>
                        <th>@lang('warehouse::app.warehouse')</th>
                        <th>@lang('warehouse::app.quantity')</th>
                        <th>@lang('app.updatedAt')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                        <tr>
                            <td>{{ $loop->iteration + ($stocks->currentPage() - 1) * $stocks->perPage() }}</td>
                            <td>
                                <span class="font-weight-semibold">{{ $stock->product->name }}</span>
                                <br><small class="text-lightest">{{ $stock->product->sku }}</small>
                            </td>
                            <td>{{ $stock->warehouse->name }}</td>
                            <td>
                                <span class="font-weight-semibold {{ $stock->quantity > 0 ? 'text-success' : 'text-danger' }}">{{ $stock->quantity }}</span>
                            </td>
                            <td class="text-nowrap">{{ $stock->updated_at->timezone(company()->timezone)->format(company()->date_format . ' H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-0">
                                <div class="p-5">
                                    <x-cards.no-record icon="cubes" :message="__('warehouse::app.emptyStockOnboardingTitle')" />
                                    <p class="f-14 text-dark-grey mt-3 mb-2">{{ __('warehouse::app.emptyStockOnboardingIntro') }}</p>
                                    <ul class="f-14 mb-0 pl-3 text-dark-grey">
                                        @php
                                            $poPerm = user()->permission('view_purchase_order');
                                            $invPerm = user()->permission('view_inventory');
                                        @endphp
                                        @if ($poPerm != 'none' && $poPerm != '')
                                            <li class="mb-1"><a href="{{ route('purchase-order.index') }}">{{ __('warehouse::app.linkPurchaseOrders') }}</a></li>
                                            <li class="mb-1"><a href="{{ route('delivery-orders.index') }}">{{ __('warehouse::app.linkDeliveryOrders') }}</a></li>
                                        @endif
                                        @if ($invPerm != 'none' && $invPerm != '')
                                            <li class="mb-1"><a href="{{ route('purchase-inventory.index') }}">{{ __('warehouse::app.linkInventory') }}</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($stocks->hasPages())
            <div class="w-100 d-flex justify-content-end mt-3 px-3">
                {{ $stocks->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        $('#warehouse-stock-warehouse').on('changed.bs.select', function() {
            $('#warehouse-stock-filter').submit();
        });
    </script>
@endpush
