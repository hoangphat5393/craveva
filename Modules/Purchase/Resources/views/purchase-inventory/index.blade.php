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
            <p class="mb-0 pr-3 f-14 text-dark-grey d-flex align-items-center">@lang('purchase::modules.inventory.inventoryStatus')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="inventory_status" id="inventory_status">
                    <option value="all">@lang('modules.lead.all')</option>
                    <option value="active">@lang('purchase::modules.inventory.active')</option>
                    <option value="inactive">@lang('purchase::modules.inventory.inactive')</option>
                </select>
            </div>
        </div>
        <!-- ACCOUNT TYPE END -->

        <!-- WAREHOUSE START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('warehouse::app.warehouse')</p>
            <div class="select-status d-flex">
                <select class="form-control select-picker" name="warehouse_id" id="warehouse_id">
                    <option value="all">@lang('warehouse::app.allWarehouses')</option>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">
                            {{ $warehouse->name }}{{ $warehouse->code ? ' (' . $warehouse->code . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- WAREHOUSE END -->

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
    </x-filters.filter-box>
@endsection

@php
    $addInventoryPermission = user()->permission('add_inventory');
@endphp

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Inventory Add/Export Buttons Start -->
        <input type="hidden" name="user_id" class="user_id" value={{ user()->id }}>
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($addInventoryPermission == 'all' || $addInventoryPermission == 'added')
                    <x-forms.link-primary :link="route('purchase-inventory.create')" class="mr-3 openRightModal float-left" icon="plus">
                        @lang('purchase::app.addInventory')
                    </x-forms.link-primary>

                    <x-forms.link-secondary :link="route('purchase-inventory.import')" class="mr-3 openRightModal float-left" icon="file-upload">
                        @lang('app.importExcel')
                    </x-forms.link-secondary>
                @endif
            </div>

            @if (!in_array('client', user_roles()))
                <x-datatable.actions>
                    <div class="select-status mr-3 pl-3">
                        <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                            <option value="">@lang('app.selectAction')</option>
                            <option value="delete">@lang('app.delete')</option>
                        </select>
                    </div>
                </x-datatable.actions>
            @endif
        </div>
        <!-- Inventory Add/Export Buttons End -->

        <!-- Inventory Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            <div class="alert alert-info border-0 mb-0 rounded-0">
                <i class="fa fa-info-circle mr-1"></i>
                Inventory quantity columns on this screen are synced from warehouse batch stock (real on-hand/reserved), so Sales DO ship updates are reflected immediately.
            </div>

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Inventory Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
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
            var rowdIds = $("#inventory-table-v5 input:checkbox:checked").map(function() {
                return $(this).val();
            }).get();

            var url = "{{ route('purchase_inventory.apply_quick_action') }}?row_ids=" + rowdIds;
            var $applyBtn = $('#quick-action-apply');
            var body = $('#quick-action-form').serialize();

            $applyBtn.prop('disabled', true);
            $.easyBlockUI();
            window.apiHttp.postUrlEncoded(url, body).then(function(response) {
                if (response.status == 'success') {
                    showTable();
                    resetActionButtons();
                    deSelectAll();
                    $('#quick-action-form').hide();
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
                $.easyUnblockUI();
            });
        };

        $('#inventory-table-v5').on('preXhr.dt', function(e, settings, data) {

            var dateRangePicker = $('#datatableRange').data('daterangepicker');
            var startDate = $('#datatableRange').val();
            var endDate = null;

            if (!startDate || startDate == '' || !dateRangePicker || !dateRangePicker.startDate) {
                startDate = null;
                endDate = null;
            } else {
                startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
            }

            var searchText = $('#search-text-field').val();
            var inventoryStatus = $('#inventory_status').val();
            var warehouseId = $('#warehouse_id').val();

            data['searchText'] = searchText;
            data['inventoryStatus'] = inventoryStatus;
            data['startDate'] = startDate;
            data['endDate'] = endDate;
            data['warehouseId'] = warehouseId;
        });
        const showTable = () => {
            window.LaravelDataTables["inventory-table-v5"].draw(true);
        }

        const toggleResetFilters = () => {
            const hasSearch = $('#search-text-field').val() !== '';
            const hasStatus = $('#inventory_status').val() !== 'all';
            const hasWarehouse = $('#warehouse_id').val() !== 'all';

            if (hasSearch || hasStatus || hasWarehouse) {
                $('#reset-filters').removeClass('d-none');
            } else {
                $('#reset-filters').addClass('d-none');
            }
        };

        $('#search-text-field').on('keyup', function() {
            toggleResetFilters();
            showTable();
        });

        $('#inventory_status').on('change', function() {
            toggleResetFilters();
            showTable();
        });

        $('#warehouse_id').on('change', function() {
            toggleResetFilters();
            showTable();
        });

        $('#reset-filters').click(function() {
            $('#filter-form')[0].reset();
            $('.select-picker').val('all');

            $('.select-picker').selectpicker("refresh");
            toggleResetFilters();

            showTable();
        });

        $('body').on('change', '#change-status', function() {
            var id = $(this).data('id');
            var status = $(this).val();

            if (status == 'active') {
                var confirmStatus = "@lang('purchase::messages.confirmActiveStatus')";
            } else {
                var confirmStatus = "@lang('purchase::messages.confirmInactiveStatus')";
            }

            if (id != "" && status != "") {
                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')",
                    text: confirmStatus,
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "@lang('purchase::messages.confirmStatus')",
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

                        var url = "{{ route('purchase_inventory.change_status') }}";
                        var token = "{{ csrf_token() }}";
                        var changeBody = '_token=' + encodeURIComponent(token) +
                            '&_method=POST&id=' + encodeURIComponent(id) +
                            '&status=' + encodeURIComponent(status);

                        window.apiHttp.postUrlEncoded(url, changeBody).then(function(response) {
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
            }
        });

        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('inventory-id');
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
                    var url = "{{ route('purchase-inventory.destroy', ':id') }}";
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
