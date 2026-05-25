@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar flex-wrap">
            <div id="table-actions" class="flex-grow-1 align-items-center mt-3">
                @if (in_array(user()->permission('add_production_orders'), ['all', 'added', 'owned', 'both'], true))
                    <x-forms.link-primary :link="route('production.boms.create')" class="mr-3 mb-2 float-left" icon="plus">
                        @lang('production::app.newBom')
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
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
    </div>
@endsection

@section('filter-section')
    <x-filters.filter-box>
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('production::app.manufacturedProduct')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="output_product_id" id="production-boms-output-product-filter" data-container="body" data-size="8">
                    <option value="">@lang('app.all')</option>
                    @foreach ($finishedGoodsFilter as $p)
                        <option value="{{ $p->id }}" @selected((string) request('output_product_id') === (string) $p->id)>{{ $p->name }}</option>
                    @endforeach
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
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="production-boms-search-field" placeholder="@lang('app.startTyping')" value="{{ request('searchText') }}">
                </div>
            </div>
        </div>

        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs {{ request()->filled('output_product_id') || request()->filled('searchText') ? '' : 'd-none' }}" id="production-boms-reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
    </x-filters.filter-box>
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#production-boms-table').on('preXhr.dt', function(e, settings, data) {
            data.output_product_id = $('#production-boms-output-product-filter').val();
            data.searchText = $('#production-boms-search-field').val();
        });

        const showProductionBomsTable = () => {
            window.LaravelDataTables["production-boms-table"].draw(true);
        };

        $('#production-boms-output-product-filter').on('change changed.bs.select', function() {
            if ($(this).val() !== '' || $('#production-boms-search-field').val() !== '') {
                $('#production-boms-reset-filters').removeClass('d-none');
            } else {
                $('#production-boms-reset-filters').addClass('d-none');
            }

            showProductionBomsTable();
        });

        $('#production-boms-search-field').on('keyup', function() {
            if ($(this).val() !== '' || $('#production-boms-output-product-filter').val() !== '') {
                $('#production-boms-reset-filters').removeClass('d-none');
            } else {
                $('#production-boms-reset-filters').addClass('d-none');
            }

            showProductionBomsTable();
        });

        $('body').on('click', '#production-boms-reset-filters', function(e) {
            e.preventDefault();

            $('#production-boms-output-product-filter').val('');
            $('#production-boms-search-field').val('');
            $('.select-picker').selectpicker('refresh');
            $('#production-boms-reset-filters').addClass('d-none');

            showProductionBomsTable();
        });

        $('body').on('click', '.delete-table-row', function() {
            const bomId = $(this).data('bom-id');

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
                    let url = "{{ route('production.boms.destroy', ':id') }}";
                    url = url.replace(':id', bomId);

                    window.apiHttp.delete(url, "{{ csrf_token() }}")
                        .then(function(response) {
                            if (response.status === 'success') {
                                showProductionBomsTable();
                            }
                        })
                        .catch(function(err) {
                            $.handleApiFormError(err);
                        });
                }
            });
        });
    </script>
@endpush
