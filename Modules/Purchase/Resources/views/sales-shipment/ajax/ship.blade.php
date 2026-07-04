@php
    $salesDoRoutePrefix = config('purchase.flow_naming_mode', 'compat_v2') === 'legacy' ? 'sales-shipments' : 'sales-do';
    $salesDoTermsText = \App\Support\CompanyDocumentTerms::resolveSaleOrderTerms(invoice_setting());
    $shipWarehouse = $shipment->warehouse;
    $shipWarehouseDisplay = $shipWarehouse ? (filled($shipWarehouse->code) ? $shipWarehouse->name . ' (' . $shipWarehouse->code . ')' : $shipWarehouse->name) : '--';
@endphp

<div class="row m-0">
    <div class="col-sm-12 p-0">
        <x-form id="ship-sales-shipment-form">
            <div class="add-client bg-white rounded b-shadow-4">
                <div class="px-lg-4 px-md-4 px-3 py-3">
                    <h4 class="mb-0 f-21 font-weight-normal text-capitalize">
                        @lang('purchase::app.shipDeliveryOrder')
                    </h4>
                </div>
                <hr class="m-0 border-top-grey">

                <div class="row px-lg-4 px-md-4 px-3 py-3">
                    <div class="col-md-4">
                        <div class="form-group mb-lg-0 mb-md-0 mb-4">
                            <x-forms.label class="f-14 text-dark-grey mb-12" fieldId="ship_order" :fieldLabel="__('app.order')" />
                            <input type="text" id="ship_order" class="form-control height-35 f-14 bg-light" value="{{ $shipment->order?->order_number ?: '#' . $shipment->order_id }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-lg-0 mb-md-0 mb-4">
                            <x-forms.label class="f-14 text-dark-grey mb-12" fieldId="ship_warehouse" :fieldLabel="__('purchase::modules.deliveryOrder.warehouse')" />
                            <input type="text" id="ship_warehouse" class="form-control height-35 f-14 bg-light" value="{{ $shipWarehouseDisplay }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <x-forms.label class="f-14 text-dark-grey mb-12" fieldId="ship_number" :fieldLabel="__('purchase::app.shipmentNumber')" />
                            <input type="text" id="ship_number" class="form-control height-35 f-14 bg-light" value="{{ $shipment->shipment_number }}" readonly>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="order_id" value="{{ $shipment->order_id }}">
                <input type="hidden" name="warehouse_id" value="{{ $shipment->warehouse_id }}">
                <input type="hidden" name="shipment_number" value="{{ $shipment->shipment_number }}">
                <input type="hidden" name="shipment_date" value="{{ $shipment->shipment_date?->translatedFormat(company()->date_format) }}">
                <input type="hidden" name="status" value="{{ $shipment->status }}">
                <input type="hidden" name="notes" value="{{ $shipment->notes }}">

                <div class="row px-lg-4 px-md-4 px-3 pb-3">
                    <div class="col-md-12" id="sales-shipment-items">
                        @include('purchase::sales-shipment.ajax.items')
                    </div>
                </div>

                <div class="row px-lg-4 px-md-4 px-3 py-3 border-top-grey">
                    <div class="col-md-6 col-sm-12">
                        <x-forms.label class="f-14 text-dark-grey mb-12" fieldId="ship_notes" :fieldLabel="__('app.note')" />
                        <p class="text-dark-grey mb-0">{!! filled($shipment->notes) ? nl2br(e($shipment->notes)) : '--' !!}</p>
                    </div>
                    @include('partials.company-document-terms-readonly', [
                        'termsText' => $salesDoTermsText,
                        'label' => __('modules.invoiceSettings.saleOrderAndDeliveryOrderTerms'),
                        'wrapperClass' => 'col-md-6 col-sm-12 c-inv-note-terms',
                    ])
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="ship-sales-shipment-button" class="mr-3" icon="truck">@lang('purchase::app.ship')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route($salesDoRoutePrefix . '.show', $shipment->id)" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    (function() {
        $('#ship-sales-shipment-button').on('click', function() {
            if (typeof window.validateSalesShipmentRows === 'function') {
                if (typeof window.syncAllShipmentBatchRows === 'function') {
                    window.syncAllShipmentBatchRows();
                }
                const rowError = window.validateSalesShipmentRows();
                if (rowError) {
                    Swal.fire({
                        icon: 'error',
                        text: rowError,
                        toast: true,
                        position: 'top-end',
                        timer: 4500,
                        showConfirmButton: false
                    });
                    return;
                }
            }

            const hasPositiveShipQty = $('#ship-sales-shipment-form .shipment-qty-input').toArray().some(function(input) {
                return parseFloat($(input).val() || '0') > 0;
            });

            if (!hasPositiveShipQty) {
                Swal.fire({
                    icon: 'error',
                    text: @json(__('messages.salesDoShipQuantityRequired')),
                    toast: true,
                    position: 'top-end',
                    timer: 4500,
                    showConfirmButton: false
                });
                return;
            }

            const body = $('#ship-sales-shipment-form').serialize();
            $.easyBlockUI('#ship-sales-shipment-form');
            window.apiHttp.postUrlEncoded("{{ route($salesDoRoutePrefix . '.ship', $shipment->id) }}", body).then(function(response) {
                const dest = response.redirectUrl || (response.action === 'redirect' ? response.url : null);
                if (dest) {
                    window.location.href = dest;
                    return;
                }

                window.location.reload();
            }).catch(function(err) {
                let text = err.message || 'Request failed';
                if (err.errors && typeof err.errors === 'object') {
                    const firstKey = Object.keys(err.errors)[0];
                    const firstVal = firstKey ? err.errors[firstKey] : null;
                    if (Array.isArray(firstVal) && firstVal[0]) {
                        text = firstVal[0];
                    }
                }
                Swal.fire({
                    icon: 'error',
                    text: text,
                    toast: true,
                    position: 'top-end',
                    timer: 5500,
                    showConfirmButton: false
                });
            }).finally(function() {
                $.easyUnblockUI('#ship-sales-shipment-form');
            });
        });

        init(RIGHT_MODAL);
    })();
</script>
