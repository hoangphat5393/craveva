@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@php
    $addWarehousePermission = user()->permission('add_warehouses');
    $editWarehousePermission = user()->permission('edit_warehouses');
    $deleteWarehousePermission = user()->permission('delete_warehouses');
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
                    <x-forms.link-primary :link="route('warehouse.create')" class="mr-3 float-left" icon="plus">
                        @lang('warehouse::app.addNew')
                    </x-forms.link-primary>
                @endif
            </div>
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            <table class="table table-hover border-0 w-100 mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>@lang('warehouse::app.name')</th>
                        <th>@lang('warehouse::app.code')</th>
                        <th>@lang('warehouse::app.address')</th>
                        <th>@lang('app.status')</th>
                        <th>@lang('warehouse::app.isDefault')</th>
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
                                    <span class="badge badge-success">@lang('app.active')</span>
                                @else
                                    <span class="badge badge-secondary">@lang('app.inactive')</span>
                                @endif
                            </td>
                            <td>
                                @if ($warehouse->is_default)
                                    <i class="fa fa-check-circle text-success"></i>
                                @else
                                    <span class="text-lightest">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if ($editWarehousePermission == 'all' || $editWarehousePermission == 'added' || ($deleteWarehousePermission == 'all' || $deleteWarehousePermission == 'added'))
                                    <div class="dropdown">
                                        <button class="btn btn-secondary rounded f-14 px-2 py-1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fa fa-ellipsis-h"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0" aria-labelledby="dropdownMenuBtn">
                                            @if ($editWarehousePermission == 'all' || $editWarehousePermission == 'added')
                                                <a class="dropdown-item" href="{{ route('warehouse.edit', $warehouse->id) }}">@lang('app.edit')</a>
                                            @endif
                                            @if ($deleteWarehousePermission == 'all' || $deleteWarehousePermission == 'added')
                                                <a class="dropdown-item delete-warehouse" href="javascript:;" data-warehouse-id="{{ $warehouse->id }}">@lang('app.delete')</a>
                                                <form action="{{ route('warehouse.destroy', $warehouse->id) }}" method="POST" id="delete-warehouse-{{ $warehouse->id }}" class="d-none">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            @endif
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

        @if ($warehouses->hasPages())
            <div class="w-100 d-flex justify-content-end mt-3 px-3">
                {{ $warehouses->links() }}
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
