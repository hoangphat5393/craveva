@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
    <x-filters.filter-box>
        <!-- FILTER CONTENT -->
        <div class="select-box d-flex py-2 pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.search')</p>
            <div class="select-status d-flex">
                <input type="text" class="form-control form-control-sm" id="search-text-field" placeholder="@lang('app.search')">
            </div>
        </div>

        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('pricing::app.pricingTier')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="tier_id" id="tier_id">
                    <option value="all">@lang('app.all')</option>
                    <option value="none">@lang('app.none')</option>
                    @foreach ($tiers as $tier)
                        <option value="{{ $tier->id }}">{{ $tier->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- FILTER CONTENT END -->
    </x-filters.filter-box>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
            </div>
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">
            {!! $dataTable->table(['class' => 'table table-bordered table-hover toggle-circle default footable-loaded footable']) !!}
        </div>
    </div>
@endsection

@push('scripts')
    @include('sections.datatable_js')
    <script>
        $('#client-tiers-table').on('preXhr.dt', function(e, settings, data) {
            var searchText = $('#search-text-field').val();
            var tierId = $('#tier_id').val();
            data.searchText = searchText;
            data.tier_id = tierId;
        });

        const showTable = () => {
            window.LaravelDataTables["client-tiers-table"].draw();
        }

        $('#search-text-field, #tier_id').on('change keyup', function() {
            if ($('#search-text-field').val() !== "") {
                $('#reset-filters').removeClass('d-none');
            } else {
                $('#reset-filters').addClass('d-none');
            }
            showTable();
        });

        $('#reset-filters').click(function() {
            $('#filter-form')[0].reset();
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });
    </script>
@endpush
