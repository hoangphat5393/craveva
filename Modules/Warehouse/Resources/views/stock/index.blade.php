@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
    <style>
        .content-wrapper .pagination .page-link svg {
            width: 14px !important;
            height: 14px !important;
            max-width: 14px;
            max-height: 14px;
            display: inline-block;
            vertical-align: middle;
        }

        .content-wrapper .pagination .page-link {
            line-height: 1.2;
        }
    </style>
@endpush

@php
    $addStockPerm = user()->permission('add_warehouse_stock');
    $transferPerm = user()->permission('manage_warehouse_transfer');
    $grnRouteName = config('purchase.flow_naming_mode', 'compat_v2') === 'legacy' ? 'delivery-orders.index' : 'grn.index';
    $warehousePerPage = in_array((int) ($warehousePerPage ?? request('per_page', 25)), [10, 25, 50, 100], true) ? (int) ($warehousePerPage ?? request('per_page', 25)) : 25;
    $formatQuantity = static fn($value): string => rtrim(rtrim(number_format((float) $value, 4, '.', ''), '0'), '.');
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
                            <option value="{{ $w->id }}" @selected((string) $warehouseId === (string) $w->id)>
                                {{ $w->name }}{{ $w->code ? ' (' . $w->code . ')' : '' }}{{ $w->is_default ? ' - ' . __('warehouse::app.isDefault') : '' }}
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
                    <input type="text" name="search" class="form-control f-14 p-1 border-additional-grey" id="warehouse-stock-search" placeholder="@lang('app.startTyping')" value="{{ request('search') }}">
                </div>
            </div>
            <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
                <button type="submit" class="btn btn-secondary rounded f-14 p-2">
                    <i class="fa fa-search mr-1"></i> @lang('app.apply')
                </button>
            </div>
            <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
                <x-forms.button-secondary type="button" class="btn-xs {{ request()->filled('search') || request()->filled('warehouse_id') ? '' : 'd-none' }}" id="warehouse-stock-reset-filters" icon="times-circle">
                    @lang('app.clearFilters')
                </x-forms.button-secondary>
            </div>
        </x-filters.filter-box>
    </form>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center mt-3">
                @if ($addStockPerm == 'all' || $addStockPerm == 'added')
                    <x-forms.link-primary :link="route('warehouse.stock.create')" class="mr-3 float-left openRightModal" icon="plus" data-redirect-url="{{ route('warehouse.stock.index') }}">
                        @lang('warehouse::app.addStock')
                    </x-forms.link-primary>
                @endif
                @if ($transferPerm == 'all' || $transferPerm == 'added')
                    <x-forms.link-secondary :link="route('warehouse.transfer.create')" class="mr-3 float-left openRightModal" icon="exchange-alt" data-redirect-url="{{ route('warehouse.stock.index') }}">
                        @lang('warehouse::app.transferStock')
                    </x-forms.link-secondary>
                @endif
                <x-forms.link-secondary :link="route('warehouse.product-batches.index')" class="mr-3 float-left" icon="tags">
                    @lang('warehouse::app.warehouseBatchInventory')
                </x-forms.link-secondary>
            </div>
        </div>

        @isset($inventoryReconciliationWidget)
            @include('warehouse::stock.partials.inventory-reconciliation-widget', ['widget' => $inventoryReconciliationWidget])
        @endisset

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            <table class="table table-hover border-0 w-100 mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>@lang('warehouse::app.product')</th>
                        <th>@lang('warehouse::app.warehouse')</th>
                        <th>@lang('warehouse::app.warehouseType')</th>
                        <th>@lang('warehouse::app.quantity')</th>
                        <th>@lang('warehouse::app.reservedQuantity')</th>
                        <th>@lang('warehouse::app.availableQuantity')</th>
                        <th>@lang('warehouse::app.sellableQuantity')</th>
                        <th>@lang('app.updatedAt')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                        <tr>
                            <td>{{ $loop->iteration + ($stocks->currentPage() - 1) * $stocks->perPage() }}</td>
                            <td>
                                <span class="font-weight-semibold">{{ $stock->product?->name ?? 'Unknown product' }}</span>
                                <br><small class="text-lightest">{{ $stock->product?->sku ?? '--' }}</small>
                            </td>
                            <td>
                                {{ $stock->warehouse?->name ?? 'Unknown warehouse' }}{{ $stock->warehouse?->code ? ' (' . $stock->warehouse->code . ')' : '' }}
                                @if ($stock->warehouse?->is_default)
                                    <span class="badge badge-light ml-1">@lang('warehouse::app.isDefault')</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-light">
                                    @include('warehouse::partials.warehouse-type-label', ['type' => $stock->warehouse_type ?? ($stock->warehouse?->warehouse_type ?? 'normal')])
                                </span>
                            </td>
                            <td>
                                <span class="font-weight-semibold {{ $stock->quantity > 0 ? 'text-success' : 'text-danger' }}">{{ $formatQuantity($stock->quantity) }}</span>
                            </td>
                            <td>{{ $formatQuantity($stock->reserved_quantity ?? 0) }}</td>
                            <td>{{ $formatQuantity($stock->available_quantity ?? 0) }}</td>
                            <td>
                                <span class="font-weight-semibold {{ (float) ($stock->sellable_quantity ?? 0) > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $formatQuantity($stock->sellable_quantity ?? 0) }}
                                </span>
                            </td>
                            <td class="text-nowrap">{{ $stock->updated_at->timezone(company()->timezone)->format(company()->date_format . ' H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-0">
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
                                            <li class="mb-1"><a href="{{ route($grnRouteName) }}">{{ __('warehouse::app.linkDeliveryOrders') }}</a></li>
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
            <div class="warehouse-footer d-flex justify-content-between align-items-center flex-wrap mt-3 px-3 py-2 bg-white border">
                <div class="d-flex align-items-center mb-2 mb-md-0">
                    <span class="mr-2 text-dark-grey">@lang('app.show')</span>
                    <div class="select-status mr-2" style="min-width: 90px;">
                        <select class="form-control select-picker" id="warehouse-stock-per-page" data-size="4">
                            @foreach ([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}" @selected($warehousePerPage === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                    <span class="text-dark-grey">@lang('app.entries')</span>
                </div>

                <div class="d-flex align-items-center">
                    <span class="text-dark-grey mr-3">
                        @lang('app.showing') {{ $stocks->firstItem() ?? 0 }} @lang('app.to') {{ $stocks->lastItem() ?? 0 }} @lang('app.of') {{ $stocks->total() }} @lang('app.entries')
                    </span>
                    {{ $stocks->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        $('#warehouse-stock-warehouse').on('changed.bs.select', function() {
            $('#warehouse-stock-filter').submit();
        });

        $('#warehouse-stock-reset-filters').click(function() {
            window.location.href = '{{ route('warehouse.stock.index') }}';
        });

        $('#warehouse-stock-per-page').on('changed.bs.select', function() {
            const value = $(this).val() || '25';
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', value);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });
    </script>
@endpush
