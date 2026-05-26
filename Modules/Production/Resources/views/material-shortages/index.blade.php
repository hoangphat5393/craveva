@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
    <x-filters.filter-box>
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('production::app.status')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="status_scope" id="production-material-shortages-status-filter" data-container="body" data-size="8">
                    <option value="active" @selected($statusScope === 'active')>@lang('production::app.materialShortageStatusScopes.active')</option>
                    <option value="all" @selected($statusScope === 'all')>@lang('app.all')</option>
                    @foreach (['draft', 'released', 'in_progress', 'completed', 'cancelled'] as $statusKey)
                        <option value="{{ $statusKey }}" @selected($statusScope === $statusKey)>{{ __('production::app.statusLabels.' . $statusKey) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('production::app.rawMaterialWarehouse')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="warehouse_id" id="production-material-shortages-warehouse-filter" data-live-search="true" data-container="body" data-size="8">
                    <option value="">@lang('app.all')</option>
                    @foreach ($warehouseOptions as $warehouseOption)
                        <option value="{{ $warehouseOption->id }}" @selected((string) request('warehouse_id') === (string) $warehouseOption->id)>{{ $warehouseOption->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('production::app.rawMaterialProduct')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="material_id" id="production-material-shortages-material-filter" data-live-search="true" data-container="body" data-size="8">
                    <option value="">@lang('app.all')</option>
                    @foreach ($materialOptions as $materialOption)
                        <option value="{{ $materialOption->id }}" @selected((string) request('material_id') === (string) $materialOption->id)>{{ $materialOption->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="select-box d-flex py-2 px-lg-3 px-md-2 px-0 align-items-center">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="production-material-shortages-only-shortage" @checked($onlyShortage)>
                <label class="custom-control-label f-14 text-dark-grey pt-1" for="production-material-shortages-only-shortage">
                    @lang('production::app.showOnlyShortages')
                </label>
            </div>
        </div>

        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs {{ request()->filled('warehouse_id') || request()->filled('material_id') || $statusScope !== 'active' || !$onlyShortage ? '' : 'd-none' }}" id="production-material-shortages-reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
    </x-filters.filter-box>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar flex-wrap">
            <div id="table-actions" class="flex-grow-1 align-items-center mt-3">
                <x-forms.link-secondary :link="route('production.orders.index')" class="mr-3 mb-2 float-left" icon="arrow-left">
                    @lang('production::app.menuProductionOrders')
                </x-forms.link-secondary>
            </div>
        </div>

        <div class="alert alert-warning mt-3 mb-0">
            @lang('production::app.materialShortageSummaryHelp')
        </div>
        <div class="alert alert-light border mt-2 mb-0">
            {{ __('production::app.materialShortageSummaryStatusNote', ['statuses' => __('production::app.materialShortageStatusScopes.' . $statusScope)]) }}
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
    </div>
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#production-material-shortages-table').on('preXhr.dt', function(e, settings, data) {
            data.status_scope = $('#production-material-shortages-status-filter').val();
            data.warehouse_id = $('#production-material-shortages-warehouse-filter').val();
            data.material_id = $('#production-material-shortages-material-filter').val();
            data.only_shortage = $('#production-material-shortages-only-shortage').is(':checked') ? 1 : 0;
        });

        const showProductionMaterialShortagesTable = () => {
            window.LaravelDataTables["production-material-shortages-table"].draw(true);
        };

        const toggleProductionMaterialShortagesResetButton = () => {
            const hasFilters = $('#production-material-shortages-status-filter').val() !== 'active' ||
                $('#production-material-shortages-warehouse-filter').val() !== '' ||
                $('#production-material-shortages-material-filter').val() !== '' ||
                !$('#production-material-shortages-only-shortage').is(':checked');

            $('#production-material-shortages-reset-filters').toggleClass('d-none', !hasFilters);
        };

        $('#production-material-shortages-status-filter, #production-material-shortages-warehouse-filter, #production-material-shortages-material-filter').on('change changed.bs.select', function() {
            toggleProductionMaterialShortagesResetButton();
            showProductionMaterialShortagesTable();
        });

        $('#production-material-shortages-only-shortage').on('change', function() {
            toggleProductionMaterialShortagesResetButton();
            showProductionMaterialShortagesTable();
        });

        $('body').on('click', '#production-material-shortages-reset-filters', function(e) {
            e.preventDefault();

            $('#production-material-shortages-status-filter').val('active');
            $('#production-material-shortages-warehouse-filter').val('');
            $('#production-material-shortages-material-filter').val('');
            $('#production-material-shortages-only-shortage').prop('checked', true);
            $('.select-picker').selectpicker('refresh');

            toggleProductionMaterialShortagesResetButton();
            showProductionMaterialShortagesTable();
        });
    </script>
@endpush
