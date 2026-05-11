@extends('layouts.app')

@push('datatable-styles')
    <style>
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

        .production-list-footer {
            padding: 12px 16px;
            border-top: 1px solid #e8eef3;
            background: #fff;
        }
    </style>
@endpush

@section('filter-section')
    <form method="GET" action="{{ route('production.orders.index') }}" id="production-orders-filter-form">
        <x-filters.filter-box>
            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('production::app.status')</p>
                <div class="select-status">
                    <select class="form-control select-picker" name="status" id="production-orders-status-filter" data-container="body" data-size="8">
                        <option value="" @selected(!request()->filled('status'))>@lang('app.all')</option>
                        @foreach (['draft', 'released', 'in_progress', 'completed', 'cancelled'] as $st)
                            <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
                <button type="submit" class="btn btn-secondary rounded f-14 p-2">
                    <i class="fa fa-search mr-1"></i> @lang('app.apply')
                </button>
            </div>

            <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
                <x-forms.button-secondary type="button" class="btn-xs {{ request()->filled('status') ? '' : 'd-none' }}" id="production-orders-reset-filters" icon="times-circle">
                    @lang('app.clearFilters')
                </x-forms.button-secondary>
            </div>
        </x-filters.filter-box>
    </form>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar flex-wrap">
            <div id="table-actions" class="flex-grow-1 align-items-center mt-3">
                @if (in_array(user()->permission('add_production_orders'), ['all', 'added', 'owned', 'both'], true))
                    <x-forms.link-primary :link="route('production.orders.create')" class="mr-3 float-left" icon="plus">
                        {{ __('production::app.newOrder') }}
                    </x-forms.link-primary>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success mt-3 mb-0">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger mt-3 mb-0">{{ session('error') }}</div>
        @endif

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            <table class="table table-hover border-0 w-100 mb-0">
                <thead>
                    <tr>
                        <th class="f-14 text-dark-grey">ID</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.fgProduct')</th>
                        <th class="f-14 text-dark-grey">@lang('modules.invoices.unitType')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.plannedQty')</th>
                        <th class="f-14 text-dark-grey">@lang('production::app.status')</th>
                        <th class="f-14 text-dark-grey text-right">@lang('app.action')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->outputProduct?->name ?? '—' }}</td>
                            <td class="text-dark-grey">{{ $orderListFgUnitByProductId->get((string) $order->output_product_id) ?? ($orderListFgUnitByProductId->get($order->output_product_id) ?? '—') }}</td>
                            <td>{{ $order->planned_quantity }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</td>
                            <td class="text-right">
                                <a href="{{ route('production.orders.show', $order) }}" class="btn btn-secondary rounded f-14 btn-sm">
                                    <i class="fa fa-eye mr-1"></i>@lang('app.view')
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-5">
                                <x-cards.no-record icon="cubes" :message="__('messages.noRecordFound')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="production-list-footer d-flex justify-content-end align-items-center flex-wrap rounded-bottom">
                {{ $orders->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        $('body').on('click', '#production-orders-reset-filters', function(e) {
            e.preventDefault();
            window.location.href = "{{ route('production.orders.index') }}";
        });
    </script>
@endpush
