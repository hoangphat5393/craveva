@php($salesDoLabelKey = config('purchase.flow_naming_mode', 'compat_v2') === 'legacy' ? 'purchase::app.menu.salesShipments' : 'purchase::app.menu.saleDeliveryOrder')
@php($salesDoRoutePrefix = config('purchase.flow_naming_mode', 'compat_v2') === 'legacy' ? 'sales-shipments' : 'sales-do')
<div class="row">
    <div class="col-sm-12">
        <x-form id="save-sales-shipment-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang($salesDoLabelKey)
                </h4>

                <div class="row p-20">
                    <div class="col-md-4">
                        <x-forms.select fieldId="order_id" :fieldLabel="__('app.order')" fieldName="order_id" search="true">
                            <option value="">--</option>
                            @foreach ($orders as $order)
                                <option value="{{ $order->id }}" @selected(($prefillOrder?->id ?? null) === $order->id)>
                                    {{ $order->order_number ?: '#' . $order->id }}
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-4">
                        <x-forms.select fieldId="warehouse_id" :fieldLabel="__('purchase::modules.deliveryOrder.warehouse')" fieldName="warehouse_id" search="true">
                            <option value="">--</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ filled($warehouse->code) ? $warehouse->name . ' (' . $warehouse->code . ')' : $warehouse->name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="shipment_number" :fieldLabel="__('purchase::app.shipmentNumber')" fieldName="shipment_number" fieldPlaceholder="SS-000001" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.datepicker fieldId="shipment_date" fieldRequired="true" :fieldLabel="__('app.date')" fieldName="shipment_date" :fieldValue="now(company()->timezone)->translatedFormat(company()->date_format)" :fieldPlaceholder="__('placeholders.date')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status">
                            <option value="draft">Draft</option>
                            <option value="confirmed">Confirmed</option>
                        </x-forms.select>
                    </div>

                    <div class="col-md-12">
                        <x-forms.textarea fieldName="notes" fieldId="notes" :fieldLabel="__('app.note')" />
                    </div>
                </div>

                <div class="row px-20 pb-3">
                    <div class="col-md-12" id="sales-shipment-items"></div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-sales-shipment-button" class="mr-3" icon="check">@lang('app.save')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route($salesDoRoutePrefix . '.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    const loadSalesShipmentItems = (orderId, shipmentId = null, warehouseId = null) => {
        if (!orderId) {
            $('#sales-shipment-items').html('');
            return;
        }
        window.apiHttp.get("{{ route($salesDoRoutePrefix . '.get-items') }}", {
            params: {
                order_id: orderId,
                shipment_id: shipmentId,
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
    };

    $(document).ready(function() {
        datepicker('#shipment_date', {
            position: 'bl',
            ...datepickerConfig
        });

        const prefill = $('#order_id').val();
        if (prefill) {
            loadSalesShipmentItems(prefill, null, $('#warehouse_id').val());
        }

        $('#order_id').on('change', function() {
            loadSalesShipmentItems($(this).val(), null, $('#warehouse_id').val());
        });

        $('#warehouse_id').on('change', function() {
            const orderId = $('#order_id').val();
            if (!orderId) {
                return;
            }
            loadSalesShipmentItems(orderId, null, $(this).val());
        });

        $('#save-sales-shipment-button').on('click', function() {
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

            const body = $('#save-sales-shipment-form').serialize();
            $.easyBlockUI('#save-sales-shipment-form');
            window.apiHttp.postUrlEncoded("{{ route($salesDoRoutePrefix . '.store') }}", body).then(function(response) {
                const dest = response.redirectUrl || (response.action === 'redirect' ? response.url : null);
                if (dest) {
                    window.location.href = dest;
                }
            }).catch(function(err) {
                Swal.fire({
                    icon: 'error',
                    text: err.message,
                    toast: true,
                    position: 'top-end',
                    timer: 4000,
                    showConfirmButton: false
                });
            }).finally(function() {
                $.easyUnblockUI('#save-sales-shipment-form');
            });
        });

        init(RIGHT_MODAL);
    });
</script>
