@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
    <x-filters.filter-box>
        <!-- DATE START -->
        <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
            <div class="select-status d-flex">
                <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey" id="datatableRange" placeholder="@lang('placeholders.dateRange')">
            </div>
        </div>
        <!-- DATE END -->

        <!-- SEARCH BY START -->
        <div class="task-search d-flex  py-1 px-lg-3 px-0 border-right-grey align-items-center">
            <form class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
                <div class="input-group bg-grey rounded">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field" placeholder="@lang('app.startTyping')" value="{{ request('search_keyword') }}">
                </div>
            </form>
        </div>
        <!-- SEARCH BY END -->

        <!-- RESET START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs {{ request('search_keyword') || request('start_date') ? '' : 'd-none' }}" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->
    </x-filters.filter-box>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center mt-3">
                <x-forms.link-primary :link="route('delivery-orders.create')" class="mr-3 float-left openRightModal" icon="plus">
                    @lang('app.add') @lang('purchase::app.menu.deliveryOrders')
                </x-forms.link-primary>
            </div>
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white p-3">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
    </div>
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#delivery-order-table').on('preXhr.dt', function(e, settings, data) {
            const dateRangePicker = $('#datatableRange').data('daterangepicker');
            let startDate = $('#datatableRange').val();
            let endDate;

            if (startDate == '') {
                startDate = null;
                endDate = null;
            } else {
                startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
            }

            const searchText = $('#search-text-field').val();

            data['searchText'] = searchText;
            data['startDate'] = startDate;
            data['endDate'] = endDate;
        });

        const showTable = () => {
            window.LaravelDataTables["delivery-order-table"].draw();
        }

        $('#search-text-field').on('change keyup', function() {
            if ($('#search-text-field').val() != "") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            } else {
                $('#reset-filters').addClass('d-none');
                showTable();
            }
        });

        $('#reset-filters').click(function() {
            $('#datatableRange').val('');
            $('#search-text-field').val('');
            $('#reset-filters').addClass('d-none');
            showTable();
        });

        $('#datatableRange').on('apply.daterangepicker', (event, picker) => {
            showTable();
            $('#reset-filters').removeClass('d-none');
        });

        $('body').on('change', '.change-do-status', function() {
            var id = $(this).data('delivery-id');
            var status = $(this).val();
            var token = "{{ csrf_token() }}";

            var chUrl = "{{ route('delivery-orders.changeStatus', ':id') }}".replace(':id', id);
            var chBody = '_token=' + encodeURIComponent(token) + '&status=' + encodeURIComponent(status);
            window.apiHttp.postUrlEncoded(chUrl, chBody).then(function(response) {
                if (response.status == "success") {
                    showTable();
                }
            }).catch(function(err) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        text: err.message,
                        toast: true,
                        position: 'top-end',
                        timer: 4000,
                        showConfirmButton: false
                    });
                }
            });
        });

        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('delivery-orders.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    window.apiHttp.delete(url, token).then(function(response) {
                        if (response.status == "success") {
                            showTable();
                        }
                    }).catch(function(err) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                text: err.message,
                                toast: true,
                                position: 'top-end',
                                timer: 4000,
                                showConfirmButton: false
                            });
                        }
                    });
                }
            });
        });
    </script>
@endpush
