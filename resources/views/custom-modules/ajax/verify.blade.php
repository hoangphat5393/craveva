<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.moduleSettings.verifyPurchase')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="verify-form">
        <p class="bg-secondary p-2 rounded text-white">For Domain:- {{ request()->getHost() }}</p>

        <p>
            <span class="">Module: <b>{{ucwords($module)}}</b></span>
        </p>

        <div id="response-message"></div>

        <div class="row">
            <div class="col-sm-12">
                <x-forms.text fieldId="purchase_code" fieldLabel="Enter your purchase code"
                              fieldName="purchase_code" fieldRequired="true"
                              :fieldPlaceholder="__('placeholders.purchaseCode')">
                </x-forms.text>
                <input type="hidden" id="module" name="module" value="{{ $module }}">
            </div>

        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-module-verify" icon="check">Verify</x-forms.button-primary>
</div>

<script>
    $('#save-module-verify').click(function () {

        const url = "{{ route('custom-modules.verify_purchase') }}";
        const button = $('#save-module-verify');
        const buttonHtml = button.html();

        button.prop('disabled', true);
        $.easyBlockUI('#verify-form');

        window.apiHttp.postUrlEncoded(url, $('#verify-form').serialize())
            .then(function (response) {
                if (response.status === 'success') {
                    window.location.reload();
                }
            })
            .catch(function (error) {
                $.handleApiFormError(error);
            })
            .finally(function () {
                button.prop('disabled', false).html(buttonHtml);
                $.easyUnblockUI('#verify-form');
            }
        );
    });

</script>
