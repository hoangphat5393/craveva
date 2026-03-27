<!-- FRONT WIDGET SETTING START -->
<div class="modal-header">
    <h5 class="modal-title">@lang('app.update') @lang('superadmin.menu.frontWidgets')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>

<div class="modal-body">
    <div class="portlet-body">
        <x-form id="updateFrontWidget" method="PUT" class="ajax-form">
            <div class="form-group">
                <div class="row">
                    <div class="col-md-12">
                        <x-forms.text :fieldLabel="__('superadmin.frontCms.widgetName')" fieldName="name"
                                      autocomplete="off" fieldId="name" :fieldValue="$frontWidget->name"
                                      :fieldRequired="true"/>
                    </div>

                    <div class="col-md-12">

                        <x-forms.label fieldId="header_script"
                                       :fieldLabel="__('superadmin.frontCms.headerScript')" :fieldRequired="true"></x-forms.label>
                        <p>@lang("superadmin.headerScriptMessage")</p>

                        <div class="form-group my-3">
                            <textarea class="form-control ace-textarea f-14 " rows="20" name="header_script"
                                      id="header_script">{{ $frontWidget->header_script }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-12">

                        <x-forms.label fieldId="footer_script"
                                       :fieldLabel="__('superadmin.frontCms.footerScript')" :fieldRequired="true"></x-forms.label>
                        <pre>@lang("superadmin.footerScriptMessage")</pre>
                        <div class="form-group my-3">
                                <textarea class="form-control ace-textarea f-14" rows="20" name="footer_script"
                                          id="footer_script">{{ $frontWidget->footer_script }}</textarea>

                        </div>
                    </div>
                </div>
            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="mr-3 border-0">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="update-front-widget" icon="check">@lang('app.save')</x-forms.button-primary>
</div>
<script>

    $(MODAL_LG).on('shown.bs.modal', function (e) {
        $('.ace-textarea').ace({theme: 'twilight'});
        init('#createFrontWidget');
    });

    $('#update-front-widget').click(function () {
        const $btn = $('#update-front-widget');
        const prev = $btn.html();
        $.easyBlockUI('#updateFrontWidget');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + (document.loading || 'Loading...'));
        window.apiHttp.postForm("{{ route('superadmin.front-settings.front-widgets.update', $frontWidget->id) }}", document.getElementById('updateFrontWidget'))
            .then(function (response) {
                if (response.status === 'success') {
                    if (response.action === 'redirect' && response.url) {
                        window.location.href = response.url;
                    } else if (typeof response.message !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            text: response.message,
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                            customClass: { confirmButton: 'btn btn-primary' },
                            showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' }
                        });
                    }
                }
            })
            .catch(function (err) { $.handleApiFormError(err); })
            .finally(function () {
                $.easyUnblockUI('#updateFrontWidget');
                $btn.prop('disabled', false).html(prev);
            });
    });
</script>

