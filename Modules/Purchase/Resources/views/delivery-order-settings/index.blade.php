@extends('layouts.app')

@section('content')
    <div class="w-100 d-flex">

        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card>
            <div class="w-100 settings-tab-panel-root">
                <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4">
                    @method('POST')
                    <div class="row">
                        <div class="col-lg-12">
                            <p class="f-14 text-dark-grey mb-3">@lang('purchase::modules.purchaseSettings.deliveryOrderSettingsHelp')</p>
                        </div>
                        <div class="col-lg-12">
                            <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('purchase::modules.purchaseSettings.deliveryOrderTerms')" fieldId="delivery_order_terms" fieldName="delivery_order_terms" :fieldValue="$purchaseSetting->delivery_order_terms ?? ''" />
                        </div>
                    </div>
                </div>

                <div class="w-100 border-top-grey">
                    <x-setting-form-actions>
                        <x-forms.button-primary id="save-delivery-order-settings" class="mr-3" icon="check">@lang('app.save')
                        </x-forms.button-primary>
                    </x-setting-form-actions>
                </div>
            </div>
        </x-setting-card>
    </div>
@endsection

@include('partials.settings-save-success-toast-script')

@push('scripts')
    <script>
        $('#save-delivery-order-settings').click(function() {
            window.apiHttp.postUrlEncoded("{{ route('delivery-order-settings.update', $purchaseSetting->id) }}", $('#editSettings').serialize())
                .then(function(response) {
                    if (response && response.status === 'success') {
                        showSettingsSaveSuccessToast(response.message);
                    }
                })
                .catch(function(err) {
                    $.handleApiFormError(err);
                });
        });
    </script>
@endpush
