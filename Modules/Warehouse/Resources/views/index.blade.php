@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@php
    $viewWarehousePermission = user()->permission('view_warehouses');
    $addWarehousePermission = user()->permission('add_warehouses');
    $editWarehousePermission = user()->permission('edit_warehouses');
    $deleteWarehousePermission = user()->permission('delete_warehouses');
    $canViewWarehouse = $viewWarehousePermission !== 'none' && $viewWarehousePermission !== '';
    $currentSortBy = $warehouseSortBy ?? request('sort_by');
    $currentSortDir = strtolower((string) ($warehouseSortDir ?? request('sort_dir', 'asc')));
    $nextSortDir = static function (string $column) use ($currentSortBy, $currentSortDir): string {
        return $currentSortBy === $column && $currentSortDir === 'asc' ? 'desc' : 'asc';
    };
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
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center mt-3">
                @if ($addWarehousePermission == 'all' || $addWarehousePermission == 'added')
                    <x-forms.link-primary :link="route('warehouse.create')" class="mr-3 float-left openRightModal" icon="plus" data-redirect-url="{{ route('warehouse.index') }}">
                        @lang('warehouse::app.addNew')
                    </x-forms.link-primary>
                @endif
            </div>
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            <table class="table table-hover border-0 w-100 mb-0" id="warehouse-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>
                            <a class="text-dark-grey" href="{{ route('warehouse.index', array_merge(request()->except('page'), ['sort_by' => 'name', 'sort_dir' => $nextSortDir('name')])) }}">
                                @lang('warehouse::app.name')
                            </a>
                        </th>
                        <th>
                            <a class="text-dark-grey" href="{{ route('warehouse.index', array_merge(request()->except('page'), ['sort_by' => 'code', 'sort_dir' => $nextSortDir('code')])) }}">
                                @lang('warehouse::app.code')
                            </a>
                        </th>
                        <th>
                            <a class="text-dark-grey" href="{{ route('warehouse.index', array_merge(request()->except('page'), ['sort_by' => 'address', 'sort_dir' => $nextSortDir('address')])) }}">
                                @lang('warehouse::app.address')
                            </a>
                        </th>
                        <th>
                            <a class="text-dark-grey" href="{{ route('warehouse.index', array_merge(request()->except('page'), ['sort_by' => 'status', 'sort_dir' => $nextSortDir('status')])) }}">
                                @lang('app.status')
                            </a>
                        </th>
                        <th>
                            <a class="text-dark-grey" href="{{ route('warehouse.index', array_merge(request()->except('page'), ['sort_by' => 'is_default', 'sort_dir' => $nextSortDir('is_default')])) }}">
                                @lang('warehouse::app.isDefault')
                            </a>
                        </th>
                        <th class="text-right">@lang('app.action')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warehouses as $warehouse)
                        <tr>
                            <td>{{ $loop->iteration + ($warehouses->currentPage() - 1) * $warehouses->perPage() }}</td>
                            <td class="font-weight-semibold">{{ $warehouse->name }}</td>
                            <td>{{ $warehouse->code ?: '—' }}</td>
                            <td><span class="text-dark-grey">{{ \Illuminate\Support\Str::limit($warehouse->address, 60) ?: '—' }}</span></td>
                            <td>
                                @if ($warehouse->status === 'active')
                                    <span class="badge badge-success"><i class="fa fa-check mr-1"></i>@lang('app.active')</span>
                                @else
                                    <span class="badge badge-secondary"><i class="fa fa-pause mr-1"></i>@lang('app.inactive')</span>
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
                            <td colspan="7" class="p-5">
                                <x-cards.no-record icon="cubes" :message="__('messages.noRecordFound')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($warehouses instanceof \Illuminate\Pagination\AbstractPaginator && $warehouses->hasPages())
            <div class="w-100 d-flex justify-content-end mt-3 px-3">
                {{ $warehouses->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        $('#warehouse-status-filter').on('changed.bs.select', function() {
            $('#warehouse-filter-form').submit();
        });

        $('#warehouse-search-field').on('keyup', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $('#warehouse-filter-form').submit();
            }
        });

        $('#warehouse-filter-form').on('submit', function() {
            $('#warehouse-reset-filters').removeClass('d-none');
        });

        $('#warehouse-reset-filters').click(function() {
            window.location.href = '{{ route('warehouse.index') }}';
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
    </script>
@endpush
