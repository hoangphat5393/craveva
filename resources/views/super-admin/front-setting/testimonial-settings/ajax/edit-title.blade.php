<div class="modal-header">
    <h5 class="modal-title">@lang('app.edit') @lang('superadmin.menu.testimonial')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="createMethods" method="POST" class="ajax-form">
            <div class="row">
                <div class="col-lg-6">
                    <x-forms.select fieldId="language" :fieldLabel="__('superadmin.frontCms.defaultLanguage')" fieldName="language">
                        @foreach ($languageSettings as $language)
                            <option @selected($testimonialTitle->language_setting_id == $language->id)
                            data-content="<span class='flag-icon flag-icon-{{ $language->flag_code == 'en' ? 'gb' : strtolower($language->flag_code) }} flag-icon-squared'></span> {{ $language->language_name }}"
                                value="{{ $language->id }}">{{ $language->language_name }}</option>
                        @endforeach
                    </x-forms.select>
                </div>
                <div class="col-lg-6">
                    <input type="hidden" name="id" value="{{ $testimonialTitle->id }}">
                    <x-forms.text :fieldLabel="__('app.title')" fieldName="testimonial_title" autocomplete="off" fieldId="testimonial_title" :fieldValue="$testimonialTitle->testimonial_title" fieldRequired="true"/>
                </div>

            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-testimonial" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

    <script>
        $(".select-picker").selectpicker();
        $('#save-testimonial').click(function (event) {
            const $btn = $('#save-testimonial');
            const prev = $btn.html();
            $.easyBlockUI('#createMethods');
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + (document.loading || 'Loading...'));
            window.apiHttp.postUrlEncoded("{{ route('superadmin.front-settings.store_testimonial_title') }}", $('#createMethods').serialize())
                .then(function(response) {
                    if (response.status == "success") {
                        if (response.action === 'redirect' && response.url) {
                            window.location.href = response.url;
                        } else {
                            window.location.reload();
                        }
                    }
                })
                .catch(function (err) { $.handleApiFormError(err); })
                .finally(function () {
                    $.easyUnblockUI('#createMethods');
                    $btn.prop('disabled', false).html(prev);
                });
        });
    </script>
