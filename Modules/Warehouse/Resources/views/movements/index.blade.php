@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
    <form method="GET" action="{{ route('warehouse.movements.index') }}" id="warehouse-movements-filter">
        <x-filters.filter-box>
            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('warehouse::app.warehouse')</p>
                <div class="select-status">
                    <select class="form-control select-picker" name="warehouse_id" id="warehouse-movements-warehouse" data-container="body" data-size="8">
                        <option value="">@lang('warehouse::app.allWarehouses')</option>
                        @foreach ($warehouses as $w)
                            <option value="{{ $w->id }}" @selected((string) ($warehouseId ?? '') === (string) $w->id)>
                                {{ $w->name }}{{ $w->code ? ' (' . $w->code . ')' : '' }}{{ $w->is_default ? ' - ' . __('warehouse::app.isDefault') : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('warehouse::app.movementType')</p>
                <div class="select-status">
                    <select class="form-control select-picker" name="movement_type" id="warehouse-movements-type" data-container="body" data-size="8">
                        <option value="">@lang('warehouse::app.allMovementTypes')</option>
                        <option value="inbound" @selected(request('movement_type') === 'inbound')>@lang('warehouse::app.movementInbound')</option>
                        <option value="outbound" @selected(request('movement_type') === 'outbound')>@lang('warehouse::app.movementOutbound')</option>
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
                    <input type="text" name="search" class="form-control f-14 p-1 border-additional-grey" id="warehouse-movements-search" placeholder="@lang('warehouse::app.searchProduct')" value="{{ request('search') }}">
                </div>
            </div>
            <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
                <button type="submit" class="btn btn-secondary rounded f-14 p-2">
                    <i class="fa fa-search mr-1"></i> @lang('app.apply')
                </button>
            </div>
            <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
                <x-forms.button-secondary type="button" class="btn-xs {{ request()->filled('search') || request()->filled('warehouse_id') || request()->filled('movement_type') ? '' : 'd-none' }}" id="warehouse-movements-reset-filters" icon="times-circle">
                    @lang('app.clearFilters')
                </x-forms.button-secondary>
            </div>
        </x-filters.filter-box>
    </form>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            <table class="table table-hover border-0 w-100 mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>@lang('warehouse::app.dateTime')</th>
                        <th>@lang('warehouse::app.movementType')</th>
                        <th>@lang('warehouse::app.product')</th>
                        <th>@lang('warehouse::app.fromWarehouse')</th>
                        <th>@lang('warehouse::app.toWarehouse')</th>
                        <th class="text-right">@lang('warehouse::app.quantity')</th>
                        <th>@lang('warehouse::app.batch')</th>
                        <th>@lang('warehouse::app.reference')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        @php
                            $ref = $movement->reference_type;
                            $refLabel = $ref ? (str_contains($ref, '\\') ? class_basename($ref) : $ref) : '—';
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration + ($movements->currentPage() - 1) * $movements->perPage() }}</td>
                            <td class="text-nowrap">{{ $movement->created_at->timezone(company()->timezone)->format(company()->date_format . ' H:i') }}</td>
                            <td>
                                @if ($movement->movement_type === 'inbound')
                                    <span class="badge badge-success">@lang('warehouse::app.movementInbound')</span>
                                @elseif ($movement->movement_type === 'outbound')
                                    <span class="badge badge-warning">@lang('warehouse::app.movementOutbound')</span>
                                @else
                                    <span class="badge badge-secondary">{{ $movement->movement_type }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($movement->product)
                                    <span class="font-weight-semibold">{{ $movement->product->name }}</span>
                                    <br><small class="text-lightest">{{ $movement->product->sku }}</small>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if ($movement->warehouseFrom)
                                    {{ $movement->warehouseFrom->name }}{{ $movement->warehouseFrom->code ? ' (' . $movement->warehouseFrom->code . ')' : '' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if ($movement->warehouseTo)
                                    {{ $movement->warehouseTo->name }}{{ $movement->warehouseTo->code ? ' (' . $movement->warehouseTo->code . ')' : '' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-right font-weight-semibold">{{ $movement->quantity }}</td>
                            <td><span class="text-dark-grey">{{ $movement->batch_number ?: '—' }}</span></td>
                            <td>
                                <small class="text-dark-grey">{{ $refLabel }}</small>
                                @if ($movement->reference_id)
                                    <br><small class="text-lightest">#{{ $movement->reference_id }}</small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-5">
                                <x-cards.no-record icon="exchange-alt" :message="__('warehouse::app.noMovementsFound')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($movements->hasPages())
            <div class="w-100 d-flex justify-content-end mt-3 px-3">
                {{ $movements->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        $('#warehouse-movements-warehouse, #warehouse-movements-type').on('changed.bs.select', function() {
            $('#warehouse-movements-filter').submit();
        });

        $('#warehouse-movements-reset-filters').click(function() {
            window.location.href = '{{ route('warehouse.movements.index') }}';
        });
    </script>
@endpush
