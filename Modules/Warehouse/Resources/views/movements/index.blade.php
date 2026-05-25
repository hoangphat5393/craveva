@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
    <style>
        .movement-ref-cell {
            max-width: 220px;
        }

        .movement-ref-line {
            display: inline-block;
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: middle;
        }
    </style>
@endpush

@section('filter-section')
    <x-filters.filter-box>
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('warehouse::app.warehouse')</p>
            <div class="select-status">
                <select class="form-control select-picker" id="warehouse-movements-warehouse" data-container="body" data-size="8">
                    <option value="">@lang('warehouse::app.allWarehouses')</option>
                    @foreach ($warehouses as $w)
                        <option value="{{ $w->id }}">
                            {{ $w->name }}{{ $w->code ? ' (' . $w->code . ')' : '' }}{{ $w->is_default ? ' - ' . __('warehouse::app.isDefault') : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('warehouse::app.movementType')</p>
            <div class="select-status">
                <select class="form-control select-picker" id="warehouse-movements-type" data-container="body" data-size="8">
                    <option value="">@lang('warehouse::app.allMovementTypes')</option>
                    <option value="inbound">@lang('warehouse::app.inbound')</option>
                    <option value="outbound">@lang('warehouse::app.outbound')</option>
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
                <input type="text" class="form-control f-14 p-1 border-additional-grey" id="warehouse-movements-search" placeholder="@lang('warehouse::app.searchProduct')">
            </div>
        </div>
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary type="button" class="btn-xs d-none" id="warehouse-movements-reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
    </x-filters.filter-box>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center"></div>
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
    </div>
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#warehouse-movements-table').on('preXhr.dt', function(e, settings, data) {
            data.warehouse_id = $('#warehouse-movements-warehouse').val();
            data.movement_type = $('#warehouse-movements-type').val();
            data.searchText = $('#warehouse-movements-search').val();
        });

        const showWarehouseMovementsTable = () => {
            window.LaravelDataTables["warehouse-movements-table"].draw(true);
        };

        const toggleWarehouseMovementsReset = () => {
            if (
                $('#warehouse-movements-warehouse').val() !== '' ||
                $('#warehouse-movements-type').val() !== '' ||
                $('#warehouse-movements-search').val() !== ''
            ) {
                $('#warehouse-movements-reset-filters').removeClass('d-none');

                return;
            }

            $('#warehouse-movements-reset-filters').addClass('d-none');
        };

        $('#warehouse-movements-warehouse, #warehouse-movements-type').on('change changed.bs.select', function() {
            toggleWarehouseMovementsReset();
            showWarehouseMovementsTable();
        });

        $('#warehouse-movements-search').on('keyup', function() {
            toggleWarehouseMovementsReset();
            showWarehouseMovementsTable();
        });

        $('#warehouse-movements-reset-filters').click(function() {
            $('#warehouse-movements-warehouse').val('');
            $('#warehouse-movements-type').val('');
            $('#warehouse-movements-search').val('');
            $('.select-picker').selectpicker('refresh');
            $(this).addClass('d-none');

            showWarehouseMovementsTable();
        });
    </script>
@endpush
