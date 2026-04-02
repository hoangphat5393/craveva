@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')

    <x-filters.filter-box>
        <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
            <div class="select-status d-flex">
                <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey" id="datatableRange" placeholder="@lang('placeholders.dateRange')">
            </div>
        </div>

        @if (!in_array('client', user_roles()))
            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.client')</p>
                <div class="select-status">
                    <select class="form-control select-picker" id="clientID" data-live-search="true" data-size="8">
                        <option value="all">@lang('app.all')</option>
                        @foreach ($clients as $client)
                            <x-user-option :user="$client" />
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

        <div class="task-search d-flex py-1 px-lg-3 px-0 border-right-grey align-items-center">
            <form class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
                <div class="input-group bg-grey rounded">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field" placeholder="@lang('app.startTyping')">
                </div>
            </form>
        </div>

        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
    </x-filters.filter-box>

@endsection

@php
    $canImportSalesHistory = user()->permission('add_sales_history_import') == 'all';
@endphp

@section('content')
    <div class="content-wrapper">
        <div class="d-flex">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($canImportSalesHistory)
                    <x-forms.link-secondary :link="route('sales-history.import')" class="mr-3 float-left openRightModal" icon="file-upload" data-redirect-url="{{ route('sales-history.index') }}">
                        @lang('app.importExcel')
                    </x-forms.link-secondary>
                @endif
            </div>
        </div>

        <div class="alert alert-warning mt-3 mb-0">
            @lang('app.salesHistoryImportBlurb')
        </div>

        <div class="alert alert-info mt-3 mb-0">
            @lang('app.salesHistorySnapshotInfo')
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
    </div>
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $(function() {
            $.fn.dataTable.ext.errMode = 'none';

            var start = moment().clone().startOf('month');
            var end = moment();
            $('#datatableRange').daterangepicker({
                locale: daterangeLocale,
                linkedCalendars: false,
                autoUpdateInput: false,
                startDate: start,
                endDate: end,
                ranges: daterangeConfig
            }, function() {});

            // Default view should show all imported rows; only filter by date when user applies a range.
            $('#datatableRange').val('');
        });

        $('#sales-history-dt').on('error.dt', function(e, settings, techNote, message) {
            const responseText = settings?.jqXHR?.responseText || '';
            const displayMessage = responseText ? responseText.substring(0, 500) : message;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Sales history load failed',
                    text: displayMessage
                });
            } else {
                alert(displayMessage);
            }
        });

        $('#sales-history-dt').on('preXhr.dt', function(e, settings, data) {
            var dateRangePicker = $('#datatableRange').data('daterangepicker');
            var startDate = $('#datatableRange').val();
            var endDate = null;
            if (startDate == '') {
                startDate = null;
            } else {
                startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
            }
            data['startDate'] = startDate;
            data['endDate'] = endDate;
            data['clientID'] = $('#clientID').length ? $('#clientID').val() : 'all';
            data['searchText'] = $('#search-text-field').val();
        });

        const showTable = () => {
            window.LaravelDataTables["sales-history-dt"].draw(true);
        };

        $('#search-text-field').on('keyup', function() {
            if ($(this).val() != "") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            }
        });

        $('#clientID').on('change keyup', function() {
            if ($('#clientID').val() != "all") {
                $('#reset-filters').removeClass('d-none');
            }
            showTable();
        });

        $('#datatableRange').on('apply.daterangepicker', function() {
            const picker = $('#datatableRange').data('daterangepicker');
            const rangeText = picker.startDate.format('{{ company()->moment_date_format }}') + ' - ' + picker.endDate.format('{{ company()->moment_date_format }}');
            $('#datatableRange').val(rangeText);
            $('#reset-filters').removeClass('d-none');
            showTable();
        });

        $('#datatableRange').on('cancel.daterangepicker', function() {
            $('#datatableRange').val('');
            showTable();
        });

        $('#reset-filters').click(function() {
            $('#datatableRange').val('');
            $('#search-text-field').val('');
            if ($('#clientID').length) {
                $('#clientID').val('all').selectpicker('refresh');
            }
            $('#reset-filters').addClass('d-none');
            showTable();
        });
    </script>
@endpush
