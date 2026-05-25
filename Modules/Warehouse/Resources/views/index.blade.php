@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@php
    $addWarehousePermission = user()->permission('add_warehouses');
    $editWarehousePermission = user()->permission('edit_warehouses');
    $deleteWarehousePermission = user()->permission('delete_warehouses');
    $canBulkWarehouseAction = $editWarehousePermission == 'all' || $editWarehousePermission == 'added' || $deleteWarehousePermission == 'all' || $deleteWarehousePermission == 'added';
@endphp

@section('filter-section')
    <x-filters.filter-box>
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('warehouse::app.statusLabel')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="status" id="warehouse-status-filter">
                    <option value="all">@lang('app.all')</option>
                    <option value="active">@lang('app.active')</option>
                    <option value="inactive">@lang('app.inactive')</option>
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
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="warehouse-search-field" placeholder="@lang('app.startTyping')">
                </div>
            </div>
        </div>

        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="warehouse-reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
    </x-filters.filter-box>
@endsection

@section('content')
    <div class="content-wrapper">
        @if ($hasInboundConfigConflict || $hasOutboundModeConflict)
            <div class="alert alert-warning mt-3 mb-0" role="alert">
                @if ($hasInboundConfigConflict)
                    <div class="mb-1">
                        <strong>@lang('app.warning'):</strong> @lang('warehouse::app.inboundCanonicalConflictUi')
                    </div>
                @endif
                @if ($hasOutboundModeConflict)
                    <div>
                        <strong>@lang('app.warning'):</strong> @lang('warehouse::app.outboundModeConflictUi', ['mode' => $outboundMode])
                    </div>
                @endif
            </div>
        @endif

        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($addWarehousePermission == 'all' || $addWarehousePermission == 'added')
                    <x-forms.link-primary :link="route('warehouse.create')" class="mr-3 openRightModal float-left" icon="plus" data-redirect-url="{{ route('warehouse.index') }}">
                        @lang('warehouse::app.addNew')
                    </x-forms.link-primary>
                    <x-forms.link-secondary :link="route('warehouse.import')" class="mr-3 openRightModal float-left" icon="file-import" data-redirect-url="{{ route('warehouse.index') }}">
                        @lang('warehouse::app.importWarehouses')
                    </x-forms.link-secondary>
                @endif
            </div>

            @if ($canBulkWarehouseAction)
                <x-datatable.actions>
                    <div class="select-status mr-3 pl-3">
                        <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                            <option value="">@lang('app.selectAction')</option>
                            @if ($editWarehousePermission == 'all' || $editWarehousePermission == 'added')
                                <option value="change-status">@lang('warehouse::app.changeWarehouseStatus')</option>
                            @endif
                            @if ($deleteWarehousePermission == 'all' || $deleteWarehousePermission == 'added')
                                <option value="delete">@lang('app.delete')</option>
                            @endif
                        </select>
                    </div>
                    <div class="select-status mr-3 d-none quick-action-field" id="change-warehouse-status-action">
                        <select name="status" class="form-control select-picker">
                            <option value="active">@lang('app.active')</option>
                            <option value="inactive">@lang('app.inactive')</option>
                        </select>
                    </div>
                </x-datatable.actions>
            @endif
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
    </div>
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#warehouse-table').on('preXhr.dt', function(e, settings, data) {
            data.status = $('#warehouse-status-filter').val();
            data.searchText = $('#warehouse-search-field').val();
        });

        const showTable = () => {
            window.LaravelDataTables["warehouse-table"].draw(true);
        };

        $('#warehouse-status-filter').on('change changed.bs.select', function() {
            if ($(this).val() !== 'all') {
                $('#warehouse-reset-filters').removeClass('d-none');
            } else if ($('#warehouse-search-field').val() === '') {
                $('#warehouse-reset-filters').addClass('d-none');
            }

            showTable();
        });

        $('#warehouse-search-field').on('keyup', function() {
            if ($(this).val() !== '' || $('#warehouse-status-filter').val() !== 'all') {
                $('#warehouse-reset-filters').removeClass('d-none');
            } else {
                $('#warehouse-reset-filters').addClass('d-none');
            }

            showTable();
        });

        $('#warehouse-reset-filters').click(function() {
            $('#warehouse-status-filter').val('all');
            $('#warehouse-search-field').val('');

            $('.select-picker').selectpicker('refresh');
            $('#warehouse-reset-filters').addClass('d-none');

            showTable();
        });

        $('#quick-action-type').change(function() {
            const actionValue = $(this).val();

            if (actionValue !== '') {
                $('#quick-action-apply').removeAttr('disabled');

                if (actionValue === 'change-status') {
                    $('.quick-action-field').addClass('d-none');
                    $('#change-warehouse-status-action').removeClass('d-none');
                } else {
                    $('.quick-action-field').addClass('d-none');
                }
            } else {
                $('#quick-action-apply').attr('disabled', true);
                $('.quick-action-field').addClass('d-none');
            }
        });

        $('#quick-action-apply').click(function() {
            const actionValue = $('#quick-action-type').val();

            if (actionValue === 'delete') {
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

                return;
            }

            applyQuickAction();
        });

        const applyQuickAction = () => {
            const rowIds = $("#warehouse-table .select-table-row:checked").map(function() {
                return $(this).val();
            }).get();

            const url = "{{ route('warehouse.apply_quick_action') }}?row_ids=" + rowIds;

            window.apiHttp.postUrlEncoded(url, $('#quick-action-form').serialize())
                .then(function(response) {
                    if (response.status === 'success') {
                        showTable();
                        resetActionButtons();
                        deSelectAll();
                        $('#quick-action-form').hide();
                        $('.quick-action-field').addClass('d-none');
                    }
                })
                .catch(function(err) {
                    $.handleApiFormError(err);
                });
        };

        $('body').on('click', '.delete-table-row', function() {
            const id = $(this).data('warehouse-id');

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
                    let url = "{{ route('warehouse.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    window.apiHttp.delete(url, "{{ csrf_token() }}")
                        .then(function(response) {
                            if (response.status === 'success') {
                                showTable();
                                resetActionButtons();
                                deSelectAll();
                                $('#quick-action-form').hide();
                            }
                        })
                        .catch(function(err) {
                            $.handleApiFormError(err);
                        });
                }
            });
        });

        $('body').on('focus', '.change-warehouse-status', function() {
            $(this).data('prev', $(this).val());
        });

        function revertWarehouseStatus($select, fallbackValue) {
            $select.data('skip-confirm', 1);
            $select.val(fallbackValue);

            if (typeof $select.selectpicker === 'function') {
                $select.selectpicker('refresh');
            }
        }

        $('body').on('change changed.bs.select', '.change-warehouse-status', function() {
            const $select = $(this);

            if ($select.data('skip-confirm') === 1) {
                $select.removeData('skip-confirm');
                return;
            }

            const previousStatus = $select.data('prev') ?? $select.data('current-status');
            const warehouseId = $select.data('warehouse-id');
            const status = $select.val();

            if (typeof warehouseId === 'undefined') {
                return;
            }

            $select.prop('disabled', true);

            window.apiHttp.postUrlEncoded("{{ route('warehouse.change_status') }}", 'warehouseId=' + encodeURIComponent(warehouseId) +
                    '&status=' + encodeURIComponent(status) +
                    '&_token=' + encodeURIComponent("{{ csrf_token() }}"))
                .then(function(response) {
                    if (response.status === 'success') {
                        $select.data('current-status', status);
                        showTable();
                        return;
                    }

                    revertWarehouseStatus($select, previousStatus);
                })
                .catch(function(err) {
                    revertWarehouseStatus($select, previousStatus);
                    $.handleApiFormError(err);
                })
                .finally(function() {
                    $select.prop('disabled', false);
                    if (typeof $select.selectpicker === 'function') {
                        $select.selectpicker('refresh');
                    }
                });
        });
    </script>
@endpush
