<div class="modal-header">
    <h5 class="modal-title">@lang('app.add') @lang('biolinks::app.image')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<x-form id="create-block" method="POST" class="ajax-form">
    <div class="modal-body">
        <div class="row">
            <input type="hidden" name="biolink_id" value="{{ $biolinkId }}">
            <input type="hidden" name="type" value="image">

            <div class="col-lg-12 form-group">
                <x-forms.label class="my-3" fieldId="image-label"
                    :fieldLabel="__('biolinks::app.image')" fieldRequired="true">
                </x-forms.label>
                <input type="file" class="image" id="dropify" name="image"
                            data-allowed-file-extensions="png jpg jpeg bmp" data-messages-default="test" data-height="70"/>
            </div>
            <div class="col-sm-12 form-group">
                <x-forms.text fieldId="image-alt" :fieldLabel="__('biolinks::app.imageAlt')" fieldName="image_alt"
                              fieldRequired="true" :fieldPlaceholder="__('placeholders.name')">
                </x-forms.text>
            </div>
            <div class="col-sm-12">
                <x-forms.text fieldId="url" :fieldLabel="__('app.url')" fieldName="url"
                              fieldRequired="true" :fieldPlaceholder="__('placeholders.website')">
                </x-forms.text>
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="mr-3 border-0">@lang('app.close')</x-forms.button-cancel>
        <x-forms.button-primary id="save-block" icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</x-form>

<script>
    $("#dropify").dropify({
        messages: dropifyMessages
    });
    $('.select-picker').selectpicker();
    $('#save-block').on('click', function () {
            var url = "{{ route('biolink-blocks.store') }}";
            $.easyBlockUI('#create-block');
            window.apiHttp.postForm(url, document.getElementById('create-block'))
                .then(function (response) {
                    if (response.status == 'success') {
                        $(MODAL_LG).modal('hide');
                        $(RIGHT_MODAL).modal('hide');
                        localStorage.setItem('activeTab', 'blocks');
                        window.location.href= response.redirectUrl;
                    }
                })
                .catch(function (err) {
                    $.handleApiFormError(err);
                })
                .finally(function () {
                    $.easyUnblockUI('#create-block');
                })
    });
</script>
