@php($salesDoLabelKey = config('purchase.flow_naming_mode', 'compat_v2') === 'legacy' ? 'purchase::app.menu.salesShipments' : 'purchase::app.menu.saleDeliveryOrder')
@php($salesDoRoutePrefix = config('purchase.flow_naming_mode', 'compat_v2') === 'legacy' ? 'sales-shipments' : 'sales-do')
<div class="row">
    <div class="col-sm-12">
        <x-form id="update-sales-shipment-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang($salesDoLabelKey)
                </h4>

                <div class="row p-20">
                    <div class="col-md-4">
                        <x-forms.select fieldId="order_id" :fieldLabel="__('app.order')" fieldName="order_id" search="true">
                            @foreach ($orders as $order)
                                <option value="{{ $order->id }}" @selected($shipment->order_id === $order->id)>
                                    {{ $order->order_number ?: '#' . $order->id }}
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-4">
                        <x-forms.select fieldId="warehouse_id" :fieldLabel="__('purchase::modules.deliveryOrder.warehouse')" fieldName="warehouse_id" search="true">
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected($shipment->warehouse_id === $warehouse->id)>{{ filled($warehouse->code) ? $warehouse->name . ' (' . $warehouse->code . ')' : $warehouse->name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="shipment_number" :fieldLabel="__('purchase::app.shipmentNumber')" fieldName="shipment_number" :fieldValue="$shipment->shipment_number" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.datepicker fieldId="shipment_date" fieldRequired="true" :fieldLabel="__('app.date')" fieldName="shipment_date" :fieldValue="$shipment->shipment_date?->translatedFormat(company()->date_format)" :fieldPlaceholder="__('placeholders.date')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status">
                            @foreach (['draft', 'confirmed'] as $status)
                                <option value="{{ $status }}" @selected($shipment->status === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-12">
                        <div class="d-flex flex-wrap">
                            <div class="col-md-6 col-sm-12 p-0 mb-lg-0 mb-md-0 mb-3 pr-md-2">
                                <x-forms.textarea fieldName="notes" fieldId="notes" :fieldLabel="__('app.note')" :fieldValue="$shipment->notes" />
                            </div>
                            @php
                                $salesDoTermsText = \App\Support\CompanyDocumentTerms::resolveSaleOrderTerms(invoice_setting());
                            @endphp
                            @include('partials.company-document-terms-readonly', [
                                'termsText' => $salesDoTermsText,
                                'label' => __('modules.invoiceSettings.orderAndSalesDoTerms'),
                            ])
                        </div>
                    </div>
                </div>

                <div class="row px-20 pb-3">
                    <div class="col-md-12" id="sales-shipment-items"></div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="update-sales-shipment-button" class="mr-3" icon="check">@lang('app.update')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route($salesDoRoutePrefix . '.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    (function() {
        const loadSalesShipmentItemsForEdit = (orderId, warehouseId = null) => {
            if (!orderId) {
                $('#sales-shipment-items').html('');
                return;
            }
            window.apiHttp.get("{{ route($salesDoRoutePrefix . '.get-items') }}", {
                params: {
                    order_id: orderId,
                    shipment_id: "{{ $shipment->id }}",
                    warehouse_id: warehouseId || $('#warehouse_id').val() || null,
                    _token: "{{ csrf_token() }}"
                }
            }).then(function(response) {
                $('#sales-shipment-items').html(response.html || '');
                const defaultWarehouseId = response.defaultWarehouseId || null;
                const selectedWarehouse = $('#warehouse_id').val();
                if ((!selectedWarehouse || selectedWarehouse === '') && defaultWarehouseId) {
                    $('#warehouse_id').val(String(defaultWarehouseId));
                    if (typeof $.fn.selectpicker !== 'undefined') {
                        $('#warehouse_id').selectpicker('refresh');
                    }
                }
            });
        };

        $(document).ready(function() {
            datepicker('#shipment_date', {
                position: 'bl',
                ...datepickerConfig
            });

            loadSalesShipmentItemsForEdit($('#order_id').val(), $('#warehouse_id').val());
            $('#order_id').on('change', function() {
                loadSalesShipmentItemsForEdit($(this).val(), $('#warehouse_id').val());
            });

            $('#warehouse_id').on('change', function() {
                const orderId = $('#order_id').val();
                if (!orderId) {
                    return;
                }
                loadSalesShipmentItemsForEdit(orderId, $(this).val());
            });

            $('#update-sales-shipment-button').on('click', function() {
                const orderId = String($('#order_id').val() || '').trim();
                if (!orderId) {
                    Swal.fire({
                        icon: 'error',
                        text: @json(__('messages.salesDoSaveRequiresOrder')),
                        toast: true,
                        position: 'top-end',
                        timer: 5000,
                        showConfirmButton: false
                    });
                    return;
                }

                const warehouseId = String($('#warehouse_id').val() || '').trim();
                if (!warehouseId) {
                    Swal.fire({
                        icon: 'error',
                        text: 'Please select warehouse before saving.',
                        toast: true,
                        position: 'top-end',
                        timer: 4000,
                        showConfirmButton: false
                    });
                    return;
                }

                if (!$('#update-sales-shipment-form').find('input[name="order_item_id[]"]').length) {
                    Swal.fire({
                        icon: 'error',
                        text: @json(__('messages.salesDoSaveRequiresShipmentLines')),
                        toast: true,
                        position: 'top-end',
                        timer: 5500,
                        showConfirmButton: false
                    });
                    return;
                }

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

                const body = $('#update-sales-shipment-form').serialize() + '&_method=PUT';
                $.easyBlockUI('#update-sales-shipment-form');
                window.apiHttp.postUrlEncoded("{{ route($salesDoRoutePrefix . '.update', $shipment->id) }}", body).then(function(response) {
                    const dest = response.redirectUrl || (response.action === 'redirect' ? response.url : null);
                    if (dest) {
                        window.location.href = dest;
                    }
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
                    $.easyUnblockUI('#update-sales-shipment-form');
                });
            });

            init(RIGHT_MODAL);
        });
    })();
</script>
