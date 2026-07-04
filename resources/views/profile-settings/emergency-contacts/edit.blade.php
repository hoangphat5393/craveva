<x-form id="save-emergency-contact-form">
    <div class="modal-header">
        <h5 class="modal-title">@lang('modules.emergencyContact.editEmergencyContact')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    </div>
    <div class="modal-body">
        <div class="portlet-body">

                @method('PUT')
                <div class="bg-white rounded">
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 col-sm-12">
                            <div class="row">
                                <div class="col-md-6 col-sm-6">
                                    <x-forms.text fieldId="name" :fieldLabel="__('app.name')"
                                        fieldName="name" fieldRequired="true" :fieldPlaceholder="__('placeholders.name')"
                                        :fieldValue="$contact->name">
                                    </x-forms.text>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <x-forms.text fieldId="email" :fieldLabel="__('app.email')"
                                        fieldName="email" :fieldPlaceholder="__('placeholders.email')"
                                        :fieldValue="$contact->email">
                                    </x-forms.text>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <x-forms.tel fieldId="mobile" :fieldLabel="__('app.mobile')" fieldName="mobile"
                                       :fieldPlaceholder="__('placeholders.mobile')" fieldRequired="true"
                                        :fieldValue="$contact->mobile"></x-forms.tel>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <x-forms.text :fieldLabel="__('app.relationship')" fieldName="relationship"
                                        fieldId="relationship" :fieldPlaceholder="__('placeholders.relationship')"
                                        :fieldValue="$contact->relation" fieldRequired="true" />
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.address')"
                                        fieldName="address" fieldId="address" :fieldPlaceholder="__('placeholders.address')"
                                        :fieldValue="$contact->address">
                                    </x-forms.textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
        <x-forms.button-primary id="save-contact" icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</x-form>
<script>
    $('#save-contact').click(function() {
        var url = "{{ route('emergency-contacts.update', $contact->id) }}";
        const $btn = $('#save-contact');
        const previousHtml = $btn.html();

        $.easyBlockUI('#save-emergency-contact-form');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + (document.loading || 'Loading...'));

        window.apiHttp.postUrlEncoded(url, $('#save-emergency-contact-form').serialize())
            .then(function(response) {
                if (response.status == 'success') {
                    $('#example tbody').html(response.html);
                    $(MODAL_LG).modal('hide');
                }
            })
            .catch(function(err) {
                $.handleApiFormError(err);
            })
            .finally(function() {
                $.easyUnblockUI('#save-emergency-contact-form');
                $btn.prop('disabled', false).html(previousHtml);
            });
    });
</script>
