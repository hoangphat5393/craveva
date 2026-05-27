@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
    <x-filters.filter-box>
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('production::app.status')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="status" id="production-orders-status-filter" data-container="body" data-size="8">
                    <option value="all" @selected(!request()->filled('status'))>@lang('app.all')</option>
                    @foreach (['draft', 'released', 'in_progress', 'completed', 'cancelled'] as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ __('production::app.statusLabels.' . $st) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="task-search d-flex py-1 px-lg-3 px-0 border-right-grey align-items-center">
            <div class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
                <div class="input-group bg-grey rounded">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="production-orders-search-field" placeholder="@lang('app.startTyping')" value="{{ request('searchText') }}">
                </div>
            </div>
        </div>

        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs {{ request()->filled('status') || request()->filled('searchText') ? '' : 'd-none' }}" id="production-orders-reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
    </x-filters.filter-box>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar flex-wrap">
            <div id="table-actions" class="flex-grow-1 align-items-center mt-3">
                @if (in_array(user()->permission('add_production_orders'), ['all', 'added', 'owned', 'both'], true))
                    <x-forms.link-primary :link="route('production.orders.create')" data-redirect-url="{{ route('production.orders.index') }}" class="mr-3 mb-2 float-left openRightModal" icon="plus">
                        {{ __('production::app.newOrder') }}
                    </x-forms.link-primary>
                @endif
                @if (in_array(user()->permission('view_production_orders'), ['all', 'added', 'owned', 'both'], true))
                    <a href="{{ route('production.material-shortages.index') }}" class="btn btn-warning rounded f-14 p-2 text-white border-0 mr-3 mb-2 float-left">
                        <i class="fa fa-exclamation-triangle mr-1"></i>{{ __('production::app.materialShortageSummary') }}
                    </a>
                @endif
            </div>
        </div>

        @include('production::partials.flash-and-validation-alerts')

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
    </div>
@endsection


@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#production-orders-table').on('preXhr.dt', function(e, settings, data) {
            data.status = $('#production-orders-status-filter').val();
            data.searchText = $('#production-orders-search-field').val();
        });

        const showProductionOrdersTable = () => {
            window.LaravelDataTables["production-orders-table"].draw(true);
        };

        $('#production-orders-status-filter').on('change changed.bs.select', function() {
            if ($(this).val() !== 'all' || $('#production-orders-search-field').val() !== '') {
                $('#production-orders-reset-filters').removeClass('d-none');
            } else {
                $('#production-orders-reset-filters').addClass('d-none');
            }

            showProductionOrdersTable();
        });

        $('#production-orders-search-field').on('keyup', function() {
            if ($(this).val() !== '' || $('#production-orders-status-filter').val() !== 'all') {
                $('#production-orders-reset-filters').removeClass('d-none');
            } else {
                $('#production-orders-reset-filters').addClass('d-none');
            }

            showProductionOrdersTable();
        });

        $('body').on('click', '#production-orders-reset-filters', function(e) {
            e.preventDefault();

            $('#production-orders-status-filter').val('all');
            $('#production-orders-search-field').val('');
            $('.select-picker').selectpicker('refresh');
            $('#production-orders-reset-filters').addClass('d-none');

            showProductionOrdersTable();
        });
    </script>
@endpush
