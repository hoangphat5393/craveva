<x-form id="createMethods" method="POST" class="ajax-form">
    <div class="modal-header">
        <h5 class="modal-title">@lang('app.addNew') @lang('app.menu.offlinePaymentMethod')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    </div>

    <div class="modal-body">
        <div class="portlet-body">

                <div class="form-body">
                    <div class="form-group">
                        <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.offlinePayment.method')"
                            fieldPlaceholder="e.g. cash" fieldName="name" fieldId="name" fieldRequired="true"></x-forms.text>
                    </div>
                    <div class="form-group">
                        <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2"
                        :fieldLabel="__('modules.offlinePayment.description')" fieldName="description"
                        fieldId="description" fieldPlaceholder="e.g. via USD dollar" fieldRequired="true">
                        </x-forms.textarea>
                    </div>
                </div>

        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
        <x-forms.button-primary id="save-method" icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</x-form>
<script>
    //  save offline payments
    $('#save-method').click(function () {
        var $btn = $('#save-method');
        var prev = $btn.html();
        $.easyBlockUI('#createMethods');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + (document.loading || 'Loading...'));
        window.apiHttp.postUrlEncoded("{{route('superadmin.settings.global-offline-payment-setting.store')}}", $('#createMethods').serialize()).then(function (response) {
            window.location.reload();
        }).catch(function (err) {
            $.handleApiFormError(err);
        }).finally(function () {
            $.easyUnblockUI('#createMethods');
            $btn.prop('disabled', false).html(prev);
        })
    });
</script>

