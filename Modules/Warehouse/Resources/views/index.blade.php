@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
    <style>
        /* Keep Laravel pagination arrows consistent on this page. */
        .content-wrapper .pagination .page-link svg {
            width: 14px !important;
            height: 14px !important;
            max-width: 14px;
            max-height: 14px;
            display: inline-block;
            vertical-align: middle;
        }

        .content-wrapper .pagination .page-link {
            line-height: 1.2;
        }

        .warehouse-footer {
            padding: 12px 16px;
            border-top: 1px solid #e8eef3;
            background: #fff;
        }

        /* Match Product module sort indicator style. */
        .warehouse-sort-link {
            position: relative;
            padding-right: 16px;
            display: block !important;
        }

        .warehouse-sort-icon {
            position: absolute;
            right: 2px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 8px;
            color: #b8c2cc;
            line-height: 1;
        }
    </style>
@endpush

@php
    $viewWarehousePermission = user()->permission('view_warehouses');
    $addWarehousePermission = user()->permission('add_warehouses');
    $editWarehousePermission = user()->permission('edit_warehouses');
    $deleteWarehousePermission = user()->permission('delete_warehouses');
    $canViewWarehouse = $viewWarehousePermission !== 'none' && $viewWarehousePermission !== '';
    $canBulkWarehouseAction = $editWarehousePermission == 'all' || $editWarehousePermission == 'added' || $deleteWarehousePermission == 'all' || $deleteWarehousePermission == 'added';
    $currentSortBy = $warehouseSortBy ?? request('sort_by');
    $currentSortDir = strtolower((string) ($warehouseSortDir ?? request('sort_dir', 'asc')));
    $nextSortDir = static function (string $column) use ($currentSortBy, $currentSortDir): string {
        return $currentSortBy === $column && $currentSortDir === 'asc' ? 'desc' : 'asc';
    };
    $sortClass = static function (string $column) use ($currentSortBy, $currentSortDir): string {
        if ($currentSortBy !== $column) {
            return '';
        }

        return $currentSortDir === 'desc' ? 'is-active-desc' : 'is-active-asc';
    };
    $sortIndicatorClass = static function (string $column) use ($currentSortBy, $currentSortDir): string {
        if ($currentSortBy !== $column) {
            return 'fa fa-sort warehouse-sort-icon';
        }

        return $currentSortDir === 'desc' ? 'glyphicon glyphicon-chevron-down warehouse-sort-icon text-dark-grey' : 'glyphicon glyphicon-chevron-up warehouse-sort-icon text-dark-grey';
    };
    $warehousePerPage = in_array((int) ($warehousePerPage ?? request('per_page', 25)), [10, 25, 50, 100], true) ? (int) ($warehousePerPage ?? request('per_page', 25)) : 25;
@endphp

@section('filter-section')
    <form method="GET" action="{{ route('warehouse.index') }}" id="warehouse-filter-form">
        <x-filters.filter-box>
            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.status')</p>
                <div class="select-status">
                    <select class="form-control select-picker" name="status" id="warehouse-status-filter" data-container="body" data-size="8">
                        <option value="all" @selected(request('status', 'all') === 'all')>@lang('app.all')</option>
                        <option value="active" @selected(request('status') === 'active')>@lang('app.active')</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>@lang('app.inactive')</option>
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
                    <input type="text" name="search" class="form-control f-14 p-1 border-additional-grey" id="warehouse-search-field" placeholder="@lang('app.startTyping')" value="{{ request('search') }}">
                </div>
            </div>

            <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
                <button type="submit" class="btn btn-secondary rounded f-14 p-2">
                    <i class="fa fa-search mr-1"></i> @lang('app.apply')
                </button>
            </div>

            <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
                <x-forms.button-secondary type="button" class="btn-xs {{ request()->filled('search') || (request('status') && request('status') !== 'all') ? '' : 'd-none' }}" id="warehouse-reset-filters" icon="times-circle">
                    @lang('app.clearFilters')
                </x-forms.button-secondary>
            </div>
        </x-filters.filter-box>
    </form>
@endsection

@section('content')
    <div class="content-wrapper">
        <div id="warehouse-list-wrap">
            <div class="d-flex justify-content-between action-bar">
                <div id="table-actions" class="flex-grow-1 align-items-center mt-3">
                    @if ($addWarehousePermission == 'all' || $addWarehousePermission == 'added')
                        <x-forms.link-primary :link="route('warehouse.create')" class="mr-3 float-left openRightModal" icon="plus" data-redirect-url="{{ route('warehouse.index') }}">
                            @lang('warehouse::app.addNew')
                        </x-forms.link-primary>
                        <x-forms.link-secondary :link="route('warehouse.import')" class="mr-3 float-left openRightModal" icon="file-import" data-redirect-url="{{ route('warehouse.index') }}">
                            @lang('app.importExcel')
                        </x-forms.link-secondary>
                    @endif
                </div>
                @if ($canBulkWarehouseAction)
                    <div id="quick-action-form" class="d-flex align-items-center mt-3">
                        @csrf
                        <div class="select-status mr-2">
                            <select name="action_type" class="form-control select-picker" id="warehouse-quick-action-type" data-size="5" disabled>
                                <option value="">@lang('app.selectAction')</option>
                                @if ($editWarehousePermission == 'all' || $editWarehousePermission == 'added')
                                    <option value="change-status">@lang('modules.tasks.changeStatus')</option>
                                @endif
                                @if ($deleteWarehousePermission == 'all' || $deleteWarehousePermission == 'added')
                                    <option value="delete">@lang('app.delete')</option>
                                @endif
                            </select>
                        </div>
                        <div class="select-status mr-2 d-none" id="warehouse-change-status-action">
                            <select name="status" class="form-control select-picker" data-size="5">
                                <option value="active">@lang('app.active')</option>
                                <option value="inactive">@lang('app.inactive')</option>
                            </select>
                        </div>
                        <x-forms.button-primary id="warehouse-quick-action-apply" class="mr-0" disabled="true">
                            @lang('app.apply')
                        </x-forms.button-primary>
                    </div>
                @endif
            </div>

            <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
                <table class="table table-hover border-0 w-100 mb-0" id="warehouse-table">
                    <thead>
                        <tr>
                            @if ($canBulkWarehouseAction)
                                <th>
                                    <input type="checkbox" id="warehouse-select-all" class="mr-1">
                                </th>
                            @endif
                            <th>
                                <a class="text-dark-grey warehouse-sort-link {{ $sortClass('id') }}" href="{{ route('warehouse.index', array_merge(request()->except('page'), ['sort_by' => 'id', 'sort_dir' => $nextSortDir('id')])) }}">
                                    #
                                    <i class="{{ $sortIndicatorClass('id') }}"></i>
                                </a>
                            </th>
                            <th>
                                <a class="text-dark-grey warehouse-sort-link {{ $sortClass('name') }}" href="{{ route('warehouse.index', array_merge(request()->except('page'), ['sort_by' => 'name', 'sort_dir' => $nextSortDir('name')])) }}">
                                    @lang('warehouse::app.name')
                                    <i class="{{ $sortIndicatorClass('name') }}"></i>
                                </a>
                            </th>
                            <th>
                                <a class="text-dark-grey warehouse-sort-link {{ $sortClass('code') }}" href="{{ route('warehouse.index', array_merge(request()->except('page'), ['sort_by' => 'code', 'sort_dir' => $nextSortDir('code')])) }}">
                                    @lang('warehouse::app.code')
                                    <i class="{{ $sortIndicatorClass('code') }}"></i>
                                </a>
                            </th>
                            <th>
                                <a class="text-dark-grey warehouse-sort-link {{ $sortClass('address') }}" href="{{ route('warehouse.index', array_merge(request()->except('page'), ['sort_by' => 'address', 'sort_dir' => $nextSortDir('address')])) }}">
                                    @lang('warehouse::app.address')
                                    <i class="{{ $sortIndicatorClass('address') }}"></i>
                                </a>
                            </th>
                            <th>
                                <a class="text-dark-grey warehouse-sort-link {{ $sortClass('status') }}" href="{{ route('warehouse.index', array_merge(request()->except('page'), ['sort_by' => 'status', 'sort_dir' => $nextSortDir('status')])) }}">
                                    @lang('app.status')
                                    <i class="{{ $sortIndicatorClass('status') }}"></i>
                                </a>
                            </th>
                            <th>
                                <a class="text-dark-grey warehouse-sort-link {{ $sortClass('is_default') }}" href="{{ route('warehouse.index', array_merge(request()->except('page'), ['sort_by' => 'is_default', 'sort_dir' => $nextSortDir('is_default')])) }}">
                                    @lang('warehouse::app.isDefault')
                                    <i class="{{ $sortIndicatorClass('is_default') }}"></i>
                                </a>
                            </th>
                            <th class="text-right">@lang('app.action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($warehouses as $warehouse)
                            <tr>
                                @if ($canBulkWarehouseAction)
                                    <td>
                                        <input type="checkbox" class="warehouse-row-checkbox" value="{{ $warehouse->id }}">
                                    </td>
                                @endif
                                <td>{{ $loop->iteration + ($warehouses->currentPage() - 1) * $warehouses->perPage() }}</td>
                                <td class="font-weight-semibold">{{ $warehouse->name }}</td>
                                <td>{{ $warehouse->code ?: '—' }}</td>
                                <td><span class="text-dark-grey">{{ \Illuminate\Support\Str::limit($warehouse->address, 60) ?: '—' }}</span></td>
                                <td>
                                    @if ($editWarehousePermission == 'all' || $editWarehousePermission == 'added')
                                        <select class="form-control select-picker change-warehouse-status" data-size="8" data-warehouse-id="{{ $warehouse->id }}" data-current-status="{{ $warehouse->status }}">
                                            <option value="active" @selected($warehouse->status === 'active') data-content="<i class='fa fa-circle mr-2 text-light-green'></i> {{ __('app.active') }}">
                                                @lang('app.active')
                                            </option>
                                            <option value="inactive" @selected($warehouse->status === 'inactive') data-content="<i class='fa fa-circle mr-2 text-red'></i> {{ __('app.inactive') }}">
                                                @lang('app.inactive')
                                            </option>
                                        </select>
                                    @else
                                        @if ($warehouse->status === 'active')
                                            <i class="fa fa-circle mr-1 text-light-green f-10"></i>@lang('app.active')
                                        @else
                                            <i class="fa fa-circle mr-1 text-red f-10"></i>@lang('app.inactive')
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if ($warehouse->is_default)
                                        <i class="fa fa-check-circle text-success f-16" title="@lang('warehouse::app.isDefault')"></i>
                                    @else
                                        <span class="text-lightest">—</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if ($canViewWarehouse || $editWarehousePermission == 'all' || $editWarehousePermission == 'added' || ($deleteWarehousePermission == 'all' || $deleteWarehousePermission == 'added'))
                                        @php($useDropUp = $loop->remaining < 2)
                                        <div class="task_view">
                                            <div class="dropdown {{ $useDropUp ? 'dropup' : '' }}">
                                                <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link" id="warehouse-actions-{{ $warehouse->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="icon-options-vertical icons"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="warehouse-actions-{{ $warehouse->id }}" tabindex="0">
                                                    @if ($canViewWarehouse)
                                                        <a class="dropdown-item" href="{{ route('warehouse.show', $warehouse->id) }}"><i class="fa fa-eye mr-2 text-dark-grey"></i>@lang('app.view')</a>
                                                    @endif
                                                    @if ($editWarehousePermission == 'all' || $editWarehousePermission == 'added')
                                                        <a class="dropdown-item" href="{{ route('warehouse.edit', $warehouse->id) }}"><i class="fa fa-edit mr-2 text-dark-grey"></i>@lang('app.edit')</a>
                                                    @endif
                                                    @if ($deleteWarehousePermission == 'all' || $deleteWarehousePermission == 'added')
                                                        <a class="dropdown-item delete-warehouse" href="javascript:;" data-warehouse-id="{{ $warehouse->id }}"><i class="fa fa-trash-alt mr-2 text-dark-grey"></i>@lang('app.delete')</a>
                                                        <form action="{{ route('warehouse.destroy', $warehouse->id) }}" method="POST" id="delete-warehouse-{{ $warehouse->id }}" class="d-none">
                                                            @csrf
                                                            @method('DELETE')
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-lightest">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canBulkWarehouseAction ? 8 : 7 }}" class="p-5">
                                    <x-cards.no-record icon="cubes" :message="__('messages.noRecordFound')" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($warehouses instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="warehouse-footer d-flex justify-content-between align-items-center flex-wrap">
                    <div class="d-flex align-items-center mb-2 mb-md-0">
                        <span class="mr-2 text-dark-grey">@lang('app.show')</span>
                        <div class="select-status mr-2" style="min-width: 90px;">
                            <select class="form-control select-picker" id="warehouse-per-page" data-size="4">
                                @foreach ([10, 25, 50, 100] as $size)
                                    <option value="{{ $size }}" @selected($warehousePerPage === $size)>{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>
                        <span class="text-dark-grey">@lang('app.entries')</span>
                    </div>

                    <div class="d-flex align-items-center">
                        <span class="text-dark-grey mr-3">
                            @lang('app.showing') {{ $warehouses->firstItem() ?? 0 }} @lang('app.to') {{ $warehouses->lastItem() ?? 0 }} @lang('app.of') {{ $warehouses->total() }} @lang('app.entries')
                        </span>
                        {{ $warehouses->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const warehouseIndexUrl = "{{ route('warehouse.index') }}";
        let currentWarehouseListUrl = window.location.href;

        const refreshWarehouseSelectPickers = () => {
            if (typeof $.fn.selectpicker === 'function') {
                $('#warehouse-list-wrap .select-picker').selectpicker('refresh');
            }
        };

        const loadWarehouseList = (url) => {
            currentWarehouseListUrl = url;
            $.easyBlockUI('.content-wrapper');

            $.get(url, function(responseHtml) {
                const $response = $('<div>').append($.parseHTML(responseHtml));
                const $newList = $response.find('#warehouse-list-wrap');

                if ($newList.length) {
                    $('#warehouse-list-wrap').replaceWith($newList);
                    refreshWarehouseSelectPickers();
                } else {
                    window.location.href = url;
                    return;
                }

            }).fail(function() {
                window.location.href = url;
            }).always(function() {
                $.easyUnblockUI('.content-wrapper');
            });
        };

        const buildFilterUrl = () => {
            const url = new URL(currentWarehouseListUrl || window.location.href);
            const formData = $('#warehouse-filter-form').serializeArray();

            url.searchParams.delete('search');
            url.searchParams.delete('status');
            url.searchParams.delete('page');

            formData.forEach(function(item) {
                if (item.value !== '') {
                    url.searchParams.set(item.name, item.value);
                }
            });

            return url.toString();
        };

        $('#warehouse-status-filter').on('changed.bs.select', function() {
            $('#warehouse-filter-form').trigger('submit');
        });

        $('#warehouse-search-field').on('keyup', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $('#warehouse-filter-form').trigger('submit');
            }
        });

        $('#warehouse-filter-form').on('submit', function(e) {
            e.preventDefault();
            $('#warehouse-reset-filters').removeClass('d-none');
            loadWarehouseList(buildFilterUrl());
        });

        $('body').on('click', '#warehouse-reset-filters', function(e) {
            e.preventDefault();
            $('#warehouse-filter-form')[0].reset();
            $('#warehouse-status-filter').val('all');
            if (typeof $.fn.selectpicker === 'function') {
                $('#warehouse-status-filter').selectpicker('refresh');
            }
            $(this).addClass('d-none');
            loadWarehouseList(warehouseIndexUrl);
        });

        $('body').on('click', '#warehouse-list-wrap .warehouse-sort-link, #warehouse-list-wrap .pagination a', function(e) {
            e.preventDefault();
            const targetUrl = $(this).attr('href');
            if (!targetUrl) {
                return;
            }
            loadWarehouseList(targetUrl);
        });

        $('body').on('changed.bs.select', '#warehouse-per-page', function() {
            const value = $(this).val() || '25';
            const url = new URL(currentWarehouseListUrl || window.location.href);
            url.searchParams.set('per_page', value);
            url.searchParams.delete('page');
            loadWarehouseList(url.toString());
        });

        const updateWarehouseQuickActionState = () => {
            const checkedCount = $('#warehouse-list-wrap .warehouse-row-checkbox:checked').length;
            const $actionType = $('#warehouse-quick-action-type');

            if (!$actionType.length) {
                return;
            }

            if (checkedCount > 0) {
                $actionType.prop('disabled', false);
            } else {
                $actionType.prop('disabled', true).val('');
                $('#warehouse-change-status-action').addClass('d-none');
                $('#warehouse-quick-action-apply').prop('disabled', true);
            }

            if (typeof $.fn.selectpicker === 'function') {
                $actionType.selectpicker('refresh');
            }
        };

        $('body').on('change', '#warehouse-select-all', function() {
            const checked = $(this).is(':checked');
            $('#warehouse-list-wrap .warehouse-row-checkbox').prop('checked', checked);
            updateWarehouseQuickActionState();
        });

        $('body').on('change', '.warehouse-row-checkbox', function() {
            const allCount = $('#warehouse-list-wrap .warehouse-row-checkbox').length;
            const checkedCount = $('#warehouse-list-wrap .warehouse-row-checkbox:checked').length;
            $('#warehouse-select-all').prop('checked', allCount > 0 && allCount === checkedCount);
            updateWarehouseQuickActionState();
        });

        $('body').on('change', '#warehouse-quick-action-type', function() {
            const action = $(this).val();
            if (!action) {
                $('#warehouse-change-status-action').addClass('d-none');
                $('#warehouse-quick-action-apply').prop('disabled', true);
            } else {
                $('#warehouse-quick-action-apply').prop('disabled', false);
                if (action === 'change-status') {
                    $('#warehouse-change-status-action').removeClass('d-none');
                } else {
                    $('#warehouse-change-status-action').addClass('d-none');
                }
            }
        });

        $('body').on('click', '#warehouse-quick-action-apply', function(e) {
            e.preventDefault();

            const actionType = $('#warehouse-quick-action-type').val();
            const rowIds = $('#warehouse-list-wrap .warehouse-row-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (!actionType || rowIds.length === 0) {
                return;
            }

            const submitQuickAction = () => {
                const body = 'action_type=' + encodeURIComponent(actionType) +
                    '&row_ids=' + encodeURIComponent(rowIds.join(',')) +
                    '&status=' + encodeURIComponent($('#warehouse-change-status-action select[name="status"]').val() || '') +
                    '&_token=' + encodeURIComponent("{{ csrf_token() }}");

                window.apiHttp.postUrlEncoded("{{ route('warehouse.apply_quick_action') }}", body).then(function(response) {
                    if (response.status === 'success') {
                        loadWarehouseList(currentWarehouseListUrl);
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
            };

            if (actionType === 'delete') {
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
                        submitQuickAction();
                    }
                });
            } else {
                submitQuickAction();
            }
        });

        $('body').on('click', '.delete-warehouse', function() {
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
                    document.getElementById('delete-warehouse-' + id).submit();
                }
            });
        });

        $('body').on('change', '.change-warehouse-status', function() {
            const $select = $(this);
            const previousStatus = $select.data('current-status');
            const status = $select.val();
            const warehouseId = $select.data('warehouse-id');
            const url = "{{ route('warehouse.change_status') }}";
            const body = 'warehouseId=' + encodeURIComponent(warehouseId) +
                '&status=' + encodeURIComponent(status) +
                '&_token=' + encodeURIComponent("{{ csrf_token() }}");

            $select.prop('disabled', true);

            window.apiHttp.postUrlEncoded(url, body).then(function(response) {
                if (response.status === 'success') {
                    $select.data('current-status', status);
                    return;
                }

                $select.val(previousStatus).selectpicker('refresh');
            }).catch(function(err) {
                $select.val(previousStatus).selectpicker('refresh');

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
                $select.prop('disabled', false);
                $select.selectpicker('refresh');
            });
        });
    </script>
@endpush
