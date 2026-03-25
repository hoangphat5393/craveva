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
        <!-- ACCOUNT TYPE -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-3 f-14 text-dark-grey d-flex align-items-center">@lang('purchase::modules.purchaseOrder.billedStatus')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="billed_status" id="billed_status">
                    <option value="all">@lang('modules.lead.all')</option>
                    <option value="billed">@lang('purchase::modules.purchaseOrder.billed')</option>
                    <option value="unbilled">@lang('purchase::modules.purchaseOrder.unbilled')</option>
                </select>
            </div>
        </div>
        <!-- ACCOUNT TYPE END -->

        <!-- SEARCH BY TASK START -->
        <div class="task-search d-flex  py-1 px-lg-3 px-0 border-right-grey align-items-center">
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
        <!-- SEARCH BY TASK END -->

        <!-- RESET START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->

        <!-- MORE FILTERS START -->
        {{-- <x-filters.more-filter-box>
            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('modules.bankaccount.bankName')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="bank_name" id="bank_name" data-live-search="true" data-container="body"
                            data-size="8">
                            <option value="all">@lang('app.all')</option>

                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('modules.bankaccount.accountName')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="account_id" id="account_id" data-live-search="true" data-container="body"
                            data-size="8">
                            <option value="all">@lang('app.all')</option>

                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('modules.bankaccount.accountType')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="account_type" id="account_type" data-live-search="true" data-container="body"
                            data-size="8">
                            <option value="all">@lang('app.all')</option>
                            <option value="saving">@lang('modules.bankaccount.saving')</option>
                            <option value="current">@lang('modules.bankaccount.current')</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('app.status')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="status" id="status" data-live-search="true" data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            <option value="1">@lang('app.active')</option>
                            <option value="0">@lang('app.inactive')</option>
                        </select>
                    </div>
                </div>
            </div>


        </x-filters.more-filter-box> --}}
        <!-- MORE FILTERS END -->

    </x-filters.filter-box>
@endsection

@php
    $addBankAccountPermission = user()->permission('add_bankaccount');
@endphp

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center mt-3">
                @if (request()->get('view') !== 'delivery')
                    {{-- Nút tạo Purchase Order chỉ hiển thị ở view Purchase Order, không hiển thị ở Delivery Orders --}}
                    <x-forms.link-primary :link="route('purchase-order.create')" class="mr-3 float-left openRightModal" icon="plus">
                        @lang('app.add') @lang('app.order')
                    </x-forms.link-primary>
                @endif
            </div>

            {{-- <x-datatable.actions>
                <div class="select-status mr-3 pl-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
            </x-datatable.actions> --}}
        </div>
        <!-- Add Task Export Buttons End -->
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Task Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#purchase-order-table').on('preXhr.dt', function(e, settings, data) {
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
            const date_filter_on = $('#date_filter_on').val();

            var billedStatus = $('#billed_status').val();

            data['searchText'] = searchText;
            data['billedStatus'] = billedStatus;
            data['date_filter_on'] = date_filter_on;
            data['startDate'] = startDate;
            data['endDate'] = endDate;
        });
        const showTable = () => {
            window.LaravelDataTables["purchase-order-table"].draw();
        }

        $('#search-text-field, #date_filter_on, #billed_status')
            .on('change keyup',
                function() {
                    if ($('#billed_status').val() != "all") {
                        $('#reset-filters').removeClass('d-none');
                        showTable();
                    } else if ($('#search-text-field').val() != "") {
                        $('#reset-filters').removeClass('d-none');
                    } else if ($('#date_filter_on').val() != "start_date") {
                        $('#reset-filters').removeClass('d-none');
                    } else {
                        $('#reset-filters').addClass('d-none');
                    }
                    showTable();
                });


        $('body').on('click', '#reset-filters', function() {
            $('#filter-form')[0].reset();
            $('.filter-box #date_filter_on').val('start_date');
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

        $('body').on('click', '#reset-filters-2', function() {
            $('#filter-form')[0].reset();
            $('.filter-box #date_filter_on').val('start_date');
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });



        $('#quick-action-type').change(function() {
            const actionValue = $(this).val();
            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');
            } else {
                $('#quick-action-apply').attr('disabled', true);
            }
        });

        $('#quick-action-apply').click(function() {
            const actionValue = $('#quick-action-type').val();
            if (actionValue == 'delete') {
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
                        applyQuickAction();
                    }
                });

            } else {
                applyQuickAction();
            }
        });

        const applyQuickAction = () => {
            var rowdIds = $("#purchase-order-table input:checkbox:checked").map(function() {
                return $(this).val();
            }).get();

            var url = "{{ route('bankaccounts.apply_quick_action') }}?row_ids=" + rowdIds;
            var $applyBtn = $('#quick-action-apply');
            var body = $('#quick-action-form').serialize();
            $applyBtn.prop('disabled', true);
            $.easyBlockUI('.content-wrapper');
            window.apiHttp.postUrlEncoded(url, body).then(function(response) {
                if (response.status == 'success') {
                    showTable();
                    resetActionButtons();
                    deSelectAll();
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
            }).finally(function() {
                $applyBtn.prop('disabled', false);
                $.easyUnblockUI('.content-wrapper');
            });
        };

        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('order-id');
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
                    var url = "{{ route('purchase-order.destroy', ':id') }}";
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

        $('body').on('click', '.sendButton', function() {
            var id = $(this).data('order-id');
            var dataType = $(this).data('type');
            var url = "{{ route('purchase_order.send_order', ':id') }}";
            url = url.replace(':id', id);

            var token = "{{ csrf_token() }}";

            $.easyBlockUI('#purchase-order-table');
            var sendBody = '_token=' + encodeURIComponent(token) + '&data_type=' + encodeURIComponent(dataType);
            window.apiHttp.postUrlEncoded(url, sendBody).then(function(response) {
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
            }).finally(function() {
                $.easyUnblockUI('#purchase-order-table');
            });
        });

        $('body').on('change', '.change-account-status', function() {
            var id = $(this).data('account-id');
            var url = "{{ route('bankaccounts.change_status') }}";

            var token = "{{ csrf_token() }}";
            var status = $(this).val();

            if (typeof id !== 'undefined') {
                var accBody = '_token=' + encodeURIComponent(token) +
                    '&accountId=' + encodeURIComponent(id) +
                    '&status=' + encodeURIComponent(status);
                window.apiHttp.postUrlEncoded(url, accBody).then(function(response) {
                    if (response.status == "success") {
                        showTable();
                        resetActionButtons();
                        deSelectAll();
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

        $('body').on('change', '#delivery-status', function() {
            let id = $(this).data('order-id');
            let value = $(this).val();
            let url = "{{ route('purchase_order.change_status', ':id') }}";
            url = url.replace(':id', id);

            window.apiHttp.get(url, {
                params: {
                    delivery_status: value
                }
            }).then(function() {
                showTable();
                resetActionButtons();
                deSelectAll();
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
        })
    </script>
@endpush
