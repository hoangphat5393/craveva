@extends('layouts.app')

@section('content')
    <div class="w-100 d-flex ">
        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <h2 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                        @lang($pageTitle)</h2>
                </div>
            </x-slot>

            <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">
                <p class="text-lightest f-14 mb-4">@lang('warehouse::app.warehouseFlowSettingsHelp')</p>

                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <x-forms.select fieldId="sales_outbound_mode" :fieldLabel="__('warehouse::app.flow_sales_outbound_mode')" fieldName="sales_outbound_mode" fieldRequired="true">
                            <option value="shipment" @selected(($flowSettings['sales_outbound_mode'] ?? '') === 'shipment')>
                                @lang('warehouse::app.flow_mode_shipment')</option>
                            <option value="invoice" @selected(($flowSettings['sales_outbound_mode'] ?? '') === 'invoice')>
                                @lang('warehouse::app.flow_mode_invoice')</option>
                        </x-forms.select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6 mb-2">
                        <input type="hidden" name="allow_negative_stock" value="0" />
                        <x-forms.checkbox :checked="!empty($flowSettings['allow_negative_stock'])" :fieldLabel="__('warehouse::app.flow_allow_negative_stock')" fieldName="allow_negative_stock" fieldId="allow_negative_stock" fieldValue="1" />
                    </div>
                    <div class="col-lg-6 mb-2">
                        <input type="hidden" name="strict_unit_conversion" value="0" />
                        <x-forms.checkbox :checked="!empty($flowSettings['strict_unit_conversion'])" :fieldLabel="__('warehouse::app.flow_strict_unit_conversion')" fieldName="strict_unit_conversion" fieldId="strict_unit_conversion" fieldValue="1" />
                        <p class="text-muted f-12 mt-1 mb-0">@lang('warehouse::app.flow_strict_unit_conversion_hint')</p>
                    </div>
                    <div class="col-lg-6 mb-2">
                        <input type="hidden" name="inbound_from_purchase_order_delivered" value="0" />
                        <x-forms.checkbox :checked="!empty($flowSettings['inbound_from_purchase_order_delivered'])" :fieldLabel="__('warehouse::app.flow_inbound_po_delivered')" fieldName="inbound_from_purchase_order_delivered" fieldId="inbound_from_purchase_order_delivered" fieldValue="1" />
                    </div>
                    <div class="col-lg-6 mb-2">
                        <input type="hidden" name="inbound_from_delivery_order_received" value="0" />
                        <x-forms.checkbox :checked="!empty($flowSettings['inbound_from_delivery_order_received'])" :fieldLabel="__('warehouse::app.flow_inbound_do_received')" fieldName="inbound_from_delivery_order_received" fieldId="inbound_from_delivery_order_received" fieldValue="1" />
                    </div>
                    <div class="col-lg-6 mb-2">
                        <input type="hidden" name="sales_outbound_enabled" value="0" />
                        <x-forms.checkbox :checked="!empty($flowSettings['sales_outbound_enabled'])" :fieldLabel="__('warehouse::app.flow_sales_outbound_enabled')" fieldName="sales_outbound_enabled" fieldId="sales_outbound_enabled" fieldValue="1" />
                    </div>
                    <div class="col-lg-6 mb-2">
                        <input type="hidden" name="ai_order_webhook_check_stock" value="0" />
                        <x-forms.checkbox :checked="!empty($flowSettings['ai_order_webhook_check_stock'])" :fieldLabel="__('warehouse::app.flow_ai_webhook_check_stock')" fieldName="ai_order_webhook_check_stock" fieldId="ai_order_webhook_check_stock" fieldValue="1" />
                    </div>
                </div>
            </div>

            <x-slot name="action">
                <div class="w-100 border-top-grey">
                    <x-setting-form-actions>
                        <x-forms.button-primary id="save-flow-settings" class="mr-3" icon="check">@lang('app.save')
                        </x-forms.button-primary>
                    </x-setting-form-actions>
                </div>
            </x-slot>
        </x-setting-card>
    </div>
@endsection

@push('scripts')
    <script>
        $('#save-flow-settings').click(function() {
            const $btn = $('#save-flow-settings');
            $btn.prop('disabled', true);
            $.easyBlockUI('#editSettings');

            window.apiHttp.postUrlEncoded("{{ route('warehouse.company-flow-settings.update') }}", $('#editSettings').serialize())
                .then(function(response) {
                    if (response.status === 'success' && response.message && typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            text: response.message,
                            toast: true,
                            position: 'top-end',
                            timer: 3500,
                            showConfirmButton: false,
                            timerProgressBar: true,
                        });
                    }
                })
                .catch(function(err) {
                    $.handleApiFormError(err);
                })
                .finally(function() {
                    $btn.prop('disabled', false);
                    $.easyUnblockUI('#editSettings');
                });
        });
    </script>
@endpush
