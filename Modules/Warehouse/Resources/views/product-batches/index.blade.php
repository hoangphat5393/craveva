@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
    <x-filters.filter-box>
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('warehouse::app.warehouse')</p>
            <div class="select-status">
                <select class="form-control select-picker" id="warehouse-product-batches-warehouse" data-container="body" data-size="8">
                    <option value="">@lang('warehouse::app.allWarehouses')</option>
                    @foreach ($warehouses as $w)
                        <option value="{{ $w->id }}">
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
                <input type="text" class="form-control f-14 p-1 border-additional-grey" id="warehouse-product-batches-search" placeholder="@lang('app.startTyping')">
            </div>
        </div>
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary type="button" class="btn-xs d-none" id="warehouse-product-batches-reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
    </x-filters.filter-box>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                <x-forms.link-secondary :link="route('warehouse.stock.index')" class="mr-3 float-left" icon="arrow-left">
                    @lang('warehouse::app.backToWarehouseStock')
                </x-forms.link-secondary>
            </div>
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
    </div>
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#warehouse-product-batches-table').on('preXhr.dt', function(e, settings, data) {
            data.warehouse_id = $('#warehouse-product-batches-warehouse').val();
            data.searchText = $('#warehouse-product-batches-search').val();
        });

        const showWarehouseProductBatchesTable = () => {
            window.LaravelDataTables["warehouse-product-batches-table"].draw(true);
        };

        $('#warehouse-product-batches-warehouse').on('change changed.bs.select', function() {
            if ($(this).val() !== '' || $('#warehouse-product-batches-search').val() !== '') {
                $('#warehouse-product-batches-reset-filters').removeClass('d-none');
            } else {
                $('#warehouse-product-batches-reset-filters').addClass('d-none');
            }

            showWarehouseProductBatchesTable();
        });

        $('#warehouse-product-batches-search').on('keyup', function() {
            if ($(this).val() !== '' || $('#warehouse-product-batches-warehouse').val() !== '') {
                $('#warehouse-product-batches-reset-filters').removeClass('d-none');
            } else {
                $('#warehouse-product-batches-reset-filters').addClass('d-none');
            }

            showWarehouseProductBatchesTable();
        });

        $('#warehouse-product-batches-reset-filters').on('click', function() {
            $('#warehouse-product-batches-warehouse').val('');
            $('#warehouse-product-batches-search').val('');
            $('.select-picker').selectpicker('refresh');
            $(this).addClass('d-none');

            showWarehouseProductBatchesTable();
        });
    </script>
@endpush
