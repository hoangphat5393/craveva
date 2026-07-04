@section('content')
<div class="content-wrapper">
    <form action="" id="warehouse-movement-report-filter-form">
        <div class="d-flex flex-wrap my-3">
            <div class="py-2 px-0">
                <div class="d-flex pr-2 border-right-grey border-right-grey-sm-0">
                    <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
                    <div class="select-status d-flex">
                        <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                            id="warehouseMovementDateRange" placeholder="@lang('placeholders.dateRange')">
                    </div>
                </div>
            </div>

            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('warehouse::app.warehouse')</p>
                <div class="select-status">
                    <select class="form-control select-picker" id="warehouse-movement-report-warehouse" data-container="body" data-size="8">
                        <option value="">@lang('warehouse::app.allWarehouses')</option>
                        @foreach ($warehouses as $w)
                            <option value="{{ $w->id }}">
                                {{ $w->name }}{{ $w->code ? ' (' . $w->code . ')' : '' }}{{ $w->is_default ? ' - ' . __('warehouse::app.isDefault') : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('warehouse::app.movementType')</p>
                <div class="select-status">
                    <select class="form-control select-picker" id="warehouse-movement-report-type" data-container="body" data-size="8">
                        <option value="">@lang('warehouse::app.allMovementTypes')</option>
                        <option value="inbound">@lang('warehouse::app.inbound')</option>
                        <option value="outbound">@lang('warehouse::app.outbound')</option>
                    </select>
                </div>
            </div>

            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('warehouse::app.reference')</p>
                <div class="select-status">
                    <select class="form-control select-picker" id="warehouse-movement-report-reference-type" data-container="body" data-size="8">
                        <option value="">@lang('purchase::modules.reports.allReferences')</option>
                        <option value="manual_warehouse_stock">@lang('warehouse::app.reference_manual_warehouse_stock')</option>
                        <option value="manual_transfer">@lang('warehouse::app.reference_manual_transfer')</option>
                        <option value="sales_shipment">@lang('warehouse::app.reference_sales_shipment')</option>
                        <option value="sales_shipment_stock_reversal">@lang('warehouse::app.reference_sales_shipment_stock_reversal')</option>
                        <option value="purchase_order">@lang('warehouse::app.reference_purchase_order')</option>
                        <option value="grn">@lang('warehouse::app.reference_grn')</option>
                        <option value="purchase_inventory">@lang('warehouse::app.reference_purchase_inventory')</option>
                        <option value="invoice">@lang('warehouse::app.reference_invoice')</option>
                        <option value="invoice_stock_reversal">@lang('warehouse::app.reference_invoice_stock_reversal')</option>
                        <option value="production_batch">@lang('warehouse::app.reference_production_batch')</option>
                        <option value="purchase_vendor_credit">@lang('warehouse::app.reference_purchase_vendor_credit')</option>
                        <option value="purchase_vendor_credit_stock_reversal">@lang('warehouse::app.reference_purchase_vendor_credit_stock_reversal')</option>
                        <option value="credit_notes">@lang('warehouse::app.reference_credit_notes')</option>
                        <option value="credit_note_stock_reversal">@lang('warehouse::app.reference_credit_note_stock_reversal')</option>
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
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="warehouse-movement-report-search" placeholder="@lang('warehouse::app.searchProduct')">
                </div>
            </div>

            <div class="task-search d-flex py-1 px-lg-3 px-0 border-right-grey align-items-center">
                <div class="input-group bg-grey rounded w-100">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey">#</span>
                    </div>
                    <input type="number" min="1" class="form-control f-14 p-1 border-additional-grey" id="warehouse-movement-report-reference-id" placeholder="@lang('purchase::modules.reports.referenceId')">
                </div>
            </div>

            <div class="py-2 px-lg-2 px-md-2 px-0">
                <x-forms.button-secondary class="btn-xs d-none" id="warehouse-movement-report-reset-filters" icon="times-circle">
                    @lang('app.clearFilters')
                </x-forms.button-secondary>
            </div>
        </div>
    </form>

    <div class="d-flex justify-content-between action-bar">
        <div id="table-actions" class="flex-grow-1 align-items-center"></div>
    </div>

    <div class="d-flex flex-column w-tables rounded mt-4 bg-white table-responsive">
        {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
    </div>
</div>
@endsection

@push('scripts')
    @include('sections.datatable_js')
    <script src="{{ asset('vendor/jquery/daterangepicker.min.js') }}"></script>

    <script>
        $(function() {
            const start = moment().subtract(89, 'days');
            const end = moment();

            $('#warehouseMovementDateRange').daterangepicker({
                autoUpdateInput: false,
                locale: daterangeLocale,
                linkedCalendars: false,
                startDate: start,
                endDate: end,
                showDropdowns: true,
                ranges: daterangeConfig
            });

            $('#warehouseMovementDateRange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('{{ company()->moment_date_format }}') + ' ' + @json(__('app.to')) + ' ' + picker.endDate.format('{{ company()->moment_date_format }}'));
                toggleWarehouseMovementReportReset();
                showWarehouseMovementReportTable();
            });

            $('#warehouseMovementDateRange').on('cancel.daterangepicker', function() {
                $(this).val('');
                toggleWarehouseMovementReportReset();
                showWarehouseMovementReportTable();
            });
        });

        $('#warehouse-movements-table').on('preXhr.dt', function(e, settings, data) {
            const dateRangePicker = $('#warehouseMovementDateRange').data('daterangepicker');
            let startDate = $('#warehouseMovementDateRange').val();
            let endDate = null;

            if (startDate === '') {
                startDate = null;
            } else {
                startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
            }

            data.startDate = startDate;
            data.endDate = endDate;
            data.warehouse_id = $('#warehouse-movement-report-warehouse').val();
            data.movement_type = $('#warehouse-movement-report-type').val();
            data.reference_type = $('#warehouse-movement-report-reference-type').val();
            data.reference_id = $('#warehouse-movement-report-reference-id').val();
            data.searchText = $('#warehouse-movement-report-search').val();
        });

        const showWarehouseMovementReportTable = () => {
            window.LaravelDataTables["warehouse-movements-table"].draw(true);
        };

        const toggleWarehouseMovementReportReset = () => {
            if (
                $('#warehouseMovementDateRange').val() !== '' ||
                $('#warehouse-movement-report-warehouse').val() !== '' ||
                $('#warehouse-movement-report-type').val() !== '' ||
                $('#warehouse-movement-report-reference-type').val() !== '' ||
                $('#warehouse-movement-report-reference-id').val() !== '' ||
                $('#warehouse-movement-report-search').val() !== ''
            ) {
                $('#warehouse-movement-report-reset-filters').removeClass('d-none');

                return;
            }

            $('#warehouse-movement-report-reset-filters').addClass('d-none');
        };

        $('#warehouse-movement-report-warehouse, #warehouse-movement-report-type, #warehouse-movement-report-reference-type').on('change changed.bs.select', function() {
            toggleWarehouseMovementReportReset();
            showWarehouseMovementReportTable();
        });

        $('#warehouse-movement-report-search, #warehouse-movement-report-reference-id').on('keyup change', function() {
            toggleWarehouseMovementReportReset();
            showWarehouseMovementReportTable();
        });

        $('#warehouse-movement-report-reset-filters').click(function(e) {
            e.preventDefault();
            $('#warehouse-movement-report-filter-form')[0].reset();
            $('#warehouseMovementDateRange').val('');
            $('.select-picker').selectpicker('refresh');
            $(this).addClass('d-none');

            showWarehouseMovementReportTable();
        });
    </script>
@endpush
