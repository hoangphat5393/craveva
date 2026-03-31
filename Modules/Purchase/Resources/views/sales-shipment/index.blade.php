@extends('layouts.app')
@php($salesDoLabelKey = config('purchase.flow_naming_mode', 'compat_v2') === 'legacy' ? 'purchase::app.menu.salesShipments' : 'purchase::app.menu.salesDo')
@php($salesDoRoutePrefix = config('purchase.flow_naming_mode', 'compat_v2') === 'legacy' ? 'sales-shipments' : 'sales-do')
@php($canCreateSalesDo = \Modules\Purchase\Support\FlowPermission::allowsAlias('sales_do.create'))

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
    <x-filters.filter-box>
        <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
            <div class="select-status d-flex">
                <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey" id="datatableRange" placeholder="@lang('placeholders.dateRange')">
            </div>
        </div>

        <div class="task-search d-flex py-1 px-lg-3 px-0 border-right-grey align-items-center">
            <form class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
                <div class="input-group bg-grey rounded">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey"><i class="fa fa-search f-13 text-dark-grey"></i></span>
                    </div>
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field" placeholder="@lang('app.startTyping')">
                </div>
            </form>
        </div>

        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">@lang('app.clearFilters')</x-forms.button-secondary>
        </div>
    </x-filters.filter-box>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center mt-3">
                @if ($canCreateSalesDo)
                    <x-forms.link-primary :link="route($salesDoRoutePrefix . '.create')" class="mr-3 float-left openRightModal" icon="plus">
                        @lang('app.add') @lang($salesDoLabelKey)
                    </x-forms.link-primary>
                @endif
            </div>
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white p-3">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
    </div>
@endsection

@push('scripts')
    @include('sections.datatable_js')
    <script>
        const drawShipmentTable = () => window.LaravelDataTables["sales-shipment-table"].draw();

        $('#sales-shipment-table').on('preXhr.dt', function(e, settings, data) {
            const range = $('#datatableRange').data('daterangepicker');
            data.searchText = $('#search-text-field').val();
            if (!$('#datatableRange').val() || !range) {
                data.startDate = null;
                data.endDate = null;
                return;
            }
            data.startDate = range.startDate.format('{{ company()->moment_date_format }}');
            data.endDate = range.endDate.format('{{ company()->moment_date_format }}');
        });

        $('#search-text-field').on('keyup change', function() {
            $('#reset-filters').toggleClass('d-none', !$(this).val());
            drawShipmentTable();
        });
        $('#datatableRange').on('apply.daterangepicker', function() {
            $('#reset-filters').removeClass('d-none');
            drawShipmentTable();
        });
        $('#reset-filters').on('click', function() {
            $('#search-text-field').val('');
            $('#datatableRange').val('');
            $(this).addClass('d-none');
            drawShipmentTable();
        });

        const shipmentAction = (name, id) => {
            const url = "{{ url('/account') }}/{{ $salesDoRoutePrefix }}/" + id + "/" + name;
            const body = '_token=' + encodeURIComponent("{{ csrf_token() }}");

            const labels = {
                confirm: "@lang('app.confirm')",
                ship: "@lang('purchase::app.ship')",
                deliver: "@lang('purchase::modules.salesShipment.delivered')",
                reverse: "@lang('purchase::modules.salesShipment.reverse')",
                cancel: "@lang('app.cancel')"
            };

            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: labels[name] || name,
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('app.yes')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }
                window.apiHttp.postUrlEncoded(url, body).then(function() {
                    drawShipmentTable();
                }).catch(function(err) {
                    Swal.fire({
                        icon: 'error',
                        text: err.message,
                        toast: true,
                        position: 'top-end',
                        timer: 4000,
                        showConfirmButton: false
                    });
                });
            });
        };

        $('body').on('click', '.sales-shipment-confirm', function() {
            shipmentAction('confirm', $(this).data('id'));
        });
        $('body').on('click', '.sales-shipment-ship', function() {
            shipmentAction('ship', $(this).data('id'));
        });
        $('body').on('click', '.sales-shipment-deliver', function() {
            shipmentAction('deliver', $(this).data('id'));
        });
        $('body').on('click', '.sales-shipment-reverse', function() {
            shipmentAction('reverse', $(this).data('id'));
        });
        $('body').on('click', '.sales-shipment-cancel', function() {
            shipmentAction('cancel', $(this).data('id'));
        });
    </script>
@endpush
