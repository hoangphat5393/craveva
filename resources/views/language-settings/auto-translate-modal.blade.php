<div class="modal-header">
    <h5 class="modal-title">@lang('modules.languageSettings.autoTranslate')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="autoTranslateSetting" method="POST" class="form-horizontal">
            <div class="row">

                <div class="col-lg-12">
                    <x-forms.text :fieldLabel="__('modules.languageSettings.googleTranslationAPI')" :fieldPlaceholder="__('modules.languageSettings.googleTranslationAPI')" fieldName="google_key" fieldId="google_key" :fieldValue="$translateSetting->google_key" fieldRequired="true" :popover="__('modules.languageSettings.googleTranslationAPIMessage')"/>
                </div>

            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="saveAutoTranslateSetting" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>

    $(document).ready(function () {
        setTimeout(function () {
            $('[data-toggle="popover"]').popover();
        }, 500);
    });

    $('#saveAutoTranslateSetting').click(function () {
        const button = $('#saveAutoTranslateSetting');
        const buttonText = button.html();

        button.prop('disabled', true);
        $.easyBlockUI('#autoTranslateSetting');

        window.apiHttp.postUrlEncoded("{{route('language_settings.auto_translate_update')}}", $('#autoTranslateSetting').serialize())
            .then((response) => {
                if (response.status == 'success') {
                    $(MODAL_LG).modal('hide');
                }
            })
            .catch((error) => {
                if (typeof $.handleApiFormError === 'function') {
                    $.handleApiFormError(error);
                }
            })
            .finally(() => {
                button.prop('disabled', false);
                button.html(buttonText);
                $.easyUnblockUI('#autoTranslateSetting');
            });
    });

</script>

