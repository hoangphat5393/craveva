<div class="row">
    <div class="col-sm-12">
        <x-form id="update-delivery-order-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('purchase::app.menu.deliveryOrders')
                </h4>
                <div class="row p-20">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-4">
                                    <label class="f-14 text-dark-grey mb-12" for="delivery_number">@lang('app.orderNumber')</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend height-35">
                                            <span class="input-group-text border-grey f-15 bg-additional-grey px-3 text-dark" id="do-number-prefix">DO#</span>
                                        </div>
                                        <input type="text" name="delivery_number" id="delivery_number" class="form-control height-35 f-15" value="{{ $delivery->delivery_number }}" placeholder="001" aria-label="001" aria-describedby="do-number-prefix">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <x-forms.select fieldId="purchase_order_id" :fieldLabel="__('purchase::app.menu.purchaseOrder')" fieldName="purchase_order_id" search="true">
                                    <option value="">--</option>
                                    @foreach ($purchaseOrders as $order)
                                        <option value="{{ $order->id }}" @if ($delivery->purchase_order_id == $order->id) selected @endif>{{ $order->purchase_order_number }}</option>
                                    @endforeach
                                </x-forms.select>
                            </div>
                            <div class="col-md-4">
                                <x-forms.datepicker fieldId="delivery_date" fieldRequired="true" :fieldLabel="__('app.date')" fieldName="delivery_date" :fieldValue="\Carbon\Carbon::parse($delivery->delivery_date)->translatedFormat(company()->date_format)" :fieldPlaceholder="__('placeholders.date')" />
                            </div>
                            <div class="col-md-4">
                                <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status">
                                    <option value="draft" @if ($delivery->status == 'draft') selected @endif>Draft</option>
                                    <option value="inbound" @if ($delivery->status == 'inbound') selected @endif>Inbound</option>
                                    <option value="received" @if ($delivery->status == 'received') selected @endif>Received</option>
                                </x-forms.select>
                            </div>
                            <div class="col-md-4">
                                <x-forms.text fieldId="erp_shipment_reference" :fieldLabel="__('purchase::app.erpShipmentRef')" fieldName="erp_shipment_reference" :fieldValue="$delivery->erp_shipment_reference" />
                            </div>
                            <div class="col-md-4">
                                <x-forms.text fieldId="wms_shipment_reference" :fieldLabel="__('purchase::app.wmsShipmentRef')" fieldName="wms_shipment_reference" :fieldValue="$delivery->wms_shipment_reference" />
                            </div>
                        </div>

                        <x-forms.custom-field :fields="$fields" class="col-md-12"></x-forms.custom-field>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="update-delivery-order-button" class="mr-3" icon="check">@lang('app.update')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('delivery-orders.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function() {
        datepicker('#delivery_date', {
            position: 'bl',
            ...datepickerConfig
        });

        $('#update-delivery-order-form').on('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });

        $('#update-delivery-order-button').click(function() {
            const url = "{{ route('delivery-orders.update', $delivery->id) }}";
            $.easyAjax({
                url: url,
                container: '#update-delivery-order-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#update-delivery-order-button",
                data: $('#update-delivery-order-form').serialize() + '&_method=PUT',
                success: function(response) {
                    if (response.status == 'success') {
                        if ($(MODAL_XL).hasClass('show')) {
                            $(MODAL_XL).modal('hide');
                            window.location.reload();
                        } else {
                            window.location.href = response.redirectUrl;
                        }
                    }
                }
            });
        });

        $('#purchase_order_id').change(function() {
            var id = $(this).val();
            var url = "{{ route('delivery-orders.get-items') }}";
            var token = "{{ csrf_token() }}";
            $.easyAjax({
                url: url,
                type: "GET",
                data: {
                    purchase_order_id: id,
                    _token: token
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#items-list').html(response.html);
                    }
                }
            });
        });

        init(RIGHT_MODAL);
    });
</script>
