<div class="modal-header">
    <h5 class="modal-title">@lang('app.add') @lang('biolinks::app.paragraph')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<x-form id="create-block" method="POST" class="ajax-form">
    <div class="modal-body">
        <div class="row">
            <input type="hidden" name="biolink_id" value="{{ $biolinkId }}">
            <input type="hidden" name="type" value="paragraph">

            <div class="col-sm-12">
                <x-forms.textarea fieldId="text" :fieldLabel="__('app.text')" fieldName="paragraph"
                              fieldRequired="true" :fieldPlaceholder="__('placeholders.sampleText')">
                </x-forms.textarea>
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="mr-3 border-0">@lang('app.close')</x-forms.button-cancel>
        <x-forms.button-primary id="save-block" icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</x-form>


<script>

    $(".select-picker").selectpicker();

    $('#save-block').on('click', function () {
        var url = "{{ route('biolink-blocks.store') }}";
        $.easyBlockUI('#create-block');
        window.apiHttp.postUrlEncoded(url, $('#create-block').serialize())
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
            });
    });
</script>
