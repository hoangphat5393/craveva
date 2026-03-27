<x-form method="POST" class="ajax-form" id="saveDetailPayment">
    <div class="modal-header">
        <h5 class="modal-title" id="modelHeading">@lang('superadmin.menu.offlineRequest')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                aria-hidden="true">×</span></button>
    </div>
    <div class="modal-body">

        <div class="row">
            <div class="col-lg-12">
                <x-forms.file allowedFileExtensions="pdf doc xls xlsx docx rtf png jpg jpeg"
                              fieldRequired="true"
                              class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('superadmin.offlineUploadFile')"
                              fieldName="slip" fieldId="slip" />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group my-3">
                    <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2"
                                      :fieldLabel="__('app.description')" fieldRequired="true"
                                      fieldName="description"
                                      fieldId="description" :fieldPlaceholder="__('superadmin.messages.offlineDescriptionPlaceholder')">
                    </x-forms.textarea>
                </div>
            </div>
        </div>
        <input type="hidden" name="package_id" value="{{ $package_id }}">
        <input type="hidden" name="offline_id" value="{{ $offlineId }}">
        <input type="hidden" name="type" value="{{ $type }}">

    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0">@lang('app.cancel')
        </x-forms.button-cancel>
        <x-forms.button-primary id="save-offline-payment" class="mr-3" icon="check">@lang('app.save')
        </x-forms.button-primary>
    </div>
</x-form>
<script>
    $('#save-offline-payment').click(function () {
        var $btn = $('#save-offline-payment');
        var prev = $btn.html();
        $.easyBlockUI('#saveDetailPayment');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + (document.loading || 'Loading...'));
        window.apiHttp.postForm("{{ route('billing.offline-payment-submit') }}", document.getElementById('saveDetailPayment')).then(function (response) {
            if (response.status === 'success') {
                if (response.action === 'redirect' && response.url) {
                    window.location.href = response.url;
                } else if (typeof response.message !== 'undefined') {
                    var ele = $('#saveDetailPayment').find('#alert');
                    var html = '<div class="alert alert-success">' + response.message + '</div>';
                    if (ele.length === 0) {
                        $('#saveDetailPayment').find('.modal-body .row:first').prepend('<div id="alert" class="col-12">' + html + '</div>');
                    } else {
                        ele.html(html);
                    }
                }
            }
        }).catch(function (err) {
            $.handleApiFormError(err);
        }).finally(function () {
            $.easyUnblockUI('#saveDetailPayment');
            $btn.prop('disabled', false).html(prev);
        });
    });
    init(MODAL_LG);
</script>
