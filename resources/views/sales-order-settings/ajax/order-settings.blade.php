<div class="w-100">
    <x-form id="sale-order-settings-form" method="POST" class="ajax-form w-100">
        <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4">
            @method('POST')
            <div class="row">
                <div class="col-lg-3">
                    <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.invoiceSettings.orderPrefix')" :fieldPlaceholder="__('modules.invoiceSettings.orderPrefix')" fieldName="order_prefix" fieldRequired="true" fieldId="order_prefix" :fieldValue="$invoiceSetting->order_prefix" />
                </div>

                <div class="col-lg-3">
                    <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.invoiceSettings.orderNumberSeparator')" :fieldPlaceholder="__('modules.invoiceSettings.orderNumberSeparator')" fieldName="order_number_separator" fieldId="order_number_separator" :fieldValue="$invoiceSetting->order_number_separator" />
                </div>

                <div class="col-lg-3">
                    <x-forms.number class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.invoiceSettings.orderDigit')" fieldName="order_digit" fieldId="order_digit" :fieldValue="$invoiceSetting->order_digit" minValue="0" maxValue="10" />
                </div>

                <div class="col-lg-3">
                    <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.invoiceSettings.orderLookLike')" fieldName="order_look_like" fieldId="order_look_like" fieldValue="" fieldReadOnly="true" />
                </div>

                <div class="col-lg-12 mt-4">
                    <h5 class="f-15 font-weight-bold text-capitalize mb-1">@lang('modules.invoiceSettings.documentTermsSection')</h5>
                    <p class="f-13 text-dark-grey mb-3">@lang('modules.invoiceSettings.documentTermsSectionHelp')</p>
                </div>
                <div class="col-lg-12">
                    <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.invoiceSettings.orderAndSalesDoTerms')" fieldId="order_terms" fieldName="order_terms" :fieldValue="$invoiceSetting->order_terms ?? ''" />
                    <p class="f-12 text-dark-grey mb-0">@lang('modules.invoiceSettings.orderAndSalesDoTermsHelp')</p>
                </div>
            </div>
        </div>

        <div class="w-100 border-top-grey">
            <x-setting-form-actions>
                <x-forms.button-primary id="save-sale-order-settings" class="mr-3" icon="check">@lang('app.save')
                </x-forms.button-primary>
            </x-setting-form-actions>
        </div>
    </x-form>
</div>

<script>
    (function() {
        function generateSaleOrderNumberPreview() {
            var orderPrefix = $('#order_prefix').val();
            var orderNumberSeparator = $('#order_number_separator').val();
            var orderDigit = parseInt($('#order_digit').val(), 10) || 3;
            var orderZero = '';
            for (var i = 0; i < orderDigit - 1; i++) {
                orderZero += '0';
            }
            orderZero += '1';
            $('#order_look_like').val(orderPrefix + orderNumberSeparator + orderZero);
        }

        $('#order_prefix, #order_number_separator, #order_digit').off('keyup change.saleOrderSettings').on('keyup change.saleOrderSettings', generateSaleOrderNumberPreview);
        generateSaleOrderNumberPreview();

        $('#save-sale-order-settings').off('click.saleOrderSettings').on('click.saleOrderSettings', function() {
            window.apiHttp.postUrlEncoded("{{ route('sales-order-settings.update-order-settings', $invoiceSetting->id) }}", $('#sale-order-settings-form').serialize())
                .then(function(response) {
                    if (response && response.status === 'success') {
                        showSettingsSaveSuccessToast(response.message);
                    }
                })
                .catch(function(err) {
                    $.handleApiFormError(err);
                });
        });
    })();
</script>
