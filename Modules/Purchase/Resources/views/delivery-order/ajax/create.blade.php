@php
    $grnLabelKey = config('purchase.flow_naming_mode', 'compat_v2') === 'legacy' ? 'purchase::app.menu.deliveryOrders' : 'purchase::app.menu.goodsReceivedNote';
    $grnRoutePrefix = config('purchase.flow_naming_mode', 'compat_v2') === 'legacy' ? 'delivery-orders' : 'grn';
@endphp
<div class="row">
    <div class="col-sm-12">
        <x-form id="save-delivery-order-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang($grnLabelKey)
                </h4>
                <div class="row p-20">
                    <div class="col-lg-12">
                        <div class="row align-items-start">
                            <div class="col-md-4">
                                <div class="form-group my-3">
                                    <label class="f-14 text-dark-grey mb-12 d-block" for="delivery_number">@lang('purchase::app.deliveryOrderNumber')</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend height-35">
                                            <span class="input-group-text border-grey f-15 bg-additional-grey px-3 text-dark" id="do-number-prefix">DO#</span>
                                        </div>
                                        <input type="text" name="delivery_number" id="delivery_number" class="form-control height-35 f-15" value="{{ $nextDeliveryNumber ?? '001' }}" placeholder="001" aria-label="001" aria-describedby="do-number-prefix" spellcheck="false" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <x-forms.select fieldId="purchase_order_id" :fieldLabel="__('purchase::app.menu.purchaseOrder')" fieldName="purchase_order_id" search="true">
                                    <option value="">--</option>
                                    @foreach ($purchaseOrders as $order)
                                        <option value="{{ $order->id }}">{{ $order->purchase_order_number }}</option>
                                    @endforeach
                                </x-forms.select>
                            </div>
                            <div class="col-md-4">
                                <x-forms.datepicker fieldId="delivery_date" fieldRequired="true" :fieldLabel="__('app.date')" fieldName="delivery_date" :fieldValue="now(company()->timezone)->translatedFormat(company()->date_format)" :fieldPlaceholder="__('placeholders.date')" />
                            </div>
                            <div class="col-md-4">
                                <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status">
                                    <option value="draft">Draft</option>
                                    <option value="inbound">Inbound</option>
                                    <option value="received">Received</option>
                                </x-forms.select>
                            </div>
                            @if (isset($warehouses) && $warehouses->count() > 0)
                                <div class="col-md-4">
                                    <x-forms.select fieldId="warehouse_id" :fieldLabel="__('purchase::modules.deliveryOrder.warehouse')" fieldName="warehouse_id" search="true">
                                        <option value="">@lang('purchase::modules.deliveryOrder.selectWarehouse')</option>
                                        @foreach ($warehouses as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->name }}{{ !empty($wh->code) ? ' (' . $wh->code . ')' : '' }}</option>
                                        @endforeach
                                    </x-forms.select>
                                </div>
                            @endif
                            <div class="col-md-4">
                                <x-forms.text fieldId="delivery_fee" :fieldLabel="__('purchase::modules.deliveryOrder.deliveryFee')" fieldName="delivery_fee" fieldPlaceholder="0.00" />
                            </div>
                            <div class="col-md-4">
                                <x-forms.text fieldId="erp_shipment_reference" :fieldLabel="__('purchase::app.erpShipmentRef')" fieldName="erp_shipment_reference" />
                            </div>
                            <div class="col-md-4">
                                <x-forms.text fieldId="wms_shipment_reference" :fieldLabel="__('purchase::app.wmsShipmentRef')" fieldName="wms_shipment_reference" />
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12" id="items-list"></div>
                        </div>
                    </div>
                </div>

                @php
                    $grnTermsText = \App\Support\CompanyDocumentTerms::resolveGrnTerms($purchaseSetting);
                @endphp
                <div class="row p-20 border-top-grey">
                    @include('partials.company-document-terms-readonly', [
                        'termsText' => $grnTermsText,
                        'label' => __('purchase::modules.purchaseSettings.purchaseOrderAndGrnTerms'),
                        'wrapperClass' => 'col-md-12 col-sm-12 p-0 c-inv-note-terms',
                    ])
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-delivery-order-button" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route($grnRoutePrefix . '.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script>
    function initDoLineDatepickers(container) {
        var $root = container ? $(container) : $(document);
        $root.find('.do-line-expiry').each(function() {
            if ($(this).data('datepicker-initialized')) {
                return;
            }
            datepicker(this, {
                position: 'bl',
                ...datepickerConfig,
                container: document.body,
                zIndex: 9999
            });
            $(this).data('datepicker-initialized', true);
        });
        if (typeof $.fn.selectpicker !== 'undefined') {
            $root.find('#items-list select.select-picker').each(function() {
                var $s = $(this);
                if (!$s.parent().hasClass('bootstrap-select')) {
                    $s.selectpicker();
                } else {
                    $s.selectpicker('refresh');
                }
            });
        }
    }

    $(document).ready(function() {
        datepicker('#delivery_date', {
            position: 'bl',
            ...datepickerConfig
        });

        $('#save-delivery-order-form').on('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });

        function getReadableApiError(err) {
            if (err && err.errors && typeof err.errors === 'object') {
                var messages = [];
                Object.keys(err.errors).forEach(function(key) {
                    var val = err.errors[key];
                    if (Array.isArray(val)) {
                        val.forEach(function(item) {
                            if (item) {
                                messages.push(item);
                            }
                        });
                    } else if (val) {
                        messages.push(val);
                    }
                });
                if (messages.length) {
                    return messages.slice(0, 4).join('\n');
                }
            }
            return (err && err.message) ? err.message : "@lang('messages.somethingWentWrong')";
        }

        $('#save-delivery-order-button').click(function() {
            const url = "{{ route($grnRoutePrefix . '.store') }}";
            var $btn = $('#save-delivery-order-button');
            var body = $('#save-delivery-order-form').serialize();
            $btn.prop('disabled', true);
            $.easyBlockUI('#save-delivery-order-form');
            window.apiHttp.postUrlEncoded(url, body).then(function(response) {
                if (response.status == 'success') {
                    var dest = response.redirectUrl || (response.action === 'redirect' ? response.url : null);
                    if ($(MODAL_XL).hasClass('show')) {
                        $(MODAL_XL).modal('hide');
                        window.location.reload();
                    } else if (dest) {
                        window.location.href = dest;
                    }
                }
            }).catch(function(err) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        text: getReadableApiError(err),
                        toast: true,
                        position: 'top-end',
                        timer: 7000,
                        showConfirmButton: false
                    });
                }
            }).finally(function() {
                $btn.prop('disabled', false);
                $.easyUnblockUI('#save-delivery-order-form');
            });
        });

        $('#purchase_order_id').change(function() {
            var id = $(this).val();
            var url = "{{ route($grnRoutePrefix . '.get-items') }}";
            var token = "{{ csrf_token() }}";
            window.apiHttp.get(url, {
                params: {
                    purchase_order_id: id,
                    _token: token
                }
            }).then(function(response) {
                if (response.status === 'success') {
                    $('#items-list').html(response.html);
                    initDoLineDatepickers('#items-list');
                }
            }).catch(function(err) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        text: err.message,
                        toast: true,
                        position: 'top-end',
                        timer: 4000,
                        showConfirmButton: false
                    });
                }
            });
        });

        init(RIGHT_MODAL);
    });
</script>
