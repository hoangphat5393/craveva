@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@php
    $addStockPerm = user()->permission('add_warehouse_stock');
    $transferPerm = user()->permission('manage_warehouse_transfer');
    $grnRouteName = config('purchase.flow_naming_mode', 'compat_v2') === 'legacy' ? 'delivery-orders.index' : 'grn.index';
@endphp

@section('filter-section')
    <x-filters.filter-box>
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('warehouse::app.warehouse')</p>
            <div class="select-status">
                <select class="form-control select-picker" id="warehouse-stock-warehouse" data-container="body" data-size="8">
                    <option value="">@lang('warehouse::app.allWarehouses')</option>
                    @foreach ($warehouses as $w)
                        <option value="{{ $w->id }}">
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
                <input type="text" class="form-control f-14 p-1 border-additional-grey" id="warehouse-stock-search" placeholder="@lang('app.startTyping')">
            </div>
        </div>
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary type="button" class="btn-xs d-none" id="warehouse-stock-reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
    </x-filters.filter-box>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($addStockPerm == 'all' || $addStockPerm == 'added')
                    <x-forms.link-primary :link="route('warehouse.stock.create')" class="mr-3 float-left openRightModal" icon="plus" data-redirect-url="{{ route('warehouse.stock.index') }}">
                        @lang('warehouse::app.adjustStockAction')
                    </x-forms.link-primary>
                @endif
                @if ($transferPerm == 'all' || $transferPerm == 'added')
                    <x-forms.link-secondary :link="route('warehouse.transfer.create')" class="mr-3 float-left openRightModal" icon="exchange-alt" data-redirect-url="{{ route('warehouse.stock.index') }}">
                        @lang('warehouse::app.transferStock')
                    </x-forms.link-secondary>
                @endif
                <x-forms.link-secondary :link="route('warehouse.product-batches.index')" class="mr-3 float-left" icon="tags">
                    @lang('warehouse::app.stockBatches')
                </x-forms.link-secondary>
            </div>
        </div>

        @isset($inventoryReconciliationWidget)
            @include('warehouse::stock.partials.inventory-reconciliation-widget', ['widget' => $inventoryReconciliationWidget])
        @endisset

        @if ($showEmptyStockOnboarding)
            <div class="bg-white rounded mt-3 p-4">
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
        @endif

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
    </div>
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#warehouse-stock-table').on('preXhr.dt', function(e, settings, data) {
            data.warehouse_id = $('#warehouse-stock-warehouse').val();
            data.searchText = $('#warehouse-stock-search').val();
        });

        const showWarehouseStockTable = () => {
            window.LaravelDataTables["warehouse-stock-table"].draw(true);
        };

        const toggleWarehouseStockReset = () => {
            if ($('#warehouse-stock-warehouse').val() !== '' || $('#warehouse-stock-search').val() !== '') {
                $('#warehouse-stock-reset-filters').removeClass('d-none');

                return;
            }

            $('#warehouse-stock-reset-filters').addClass('d-none');
        };

        $('#warehouse-stock-warehouse').on('change changed.bs.select', function() {
            toggleWarehouseStockReset();
            showWarehouseStockTable();
        });

        $('#warehouse-stock-search').on('keyup', function() {
            toggleWarehouseStockReset();
            showWarehouseStockTable();
        });

        $('#warehouse-stock-reset-filters').click(function() {
            $('#warehouse-stock-warehouse').val('');
            $('#warehouse-stock-search').val('');
            $('.select-picker').selectpicker('refresh');
            $(this).addClass('d-none');

            showWarehouseStockTable();
        });
    </script>
@endpush
