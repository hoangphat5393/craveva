<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.projects.uploadFile')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="createTaskCategory">
        <input type="hidden" name="invoice_id" value="{{ $invoiceId }}">

        <div class="row">
            <div class="col-lg-12">
                <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.projects.uploadFile')" fieldName="file" fieldId="invoice-file" :popover="__('messages.fileFormat.ImageFile')" />
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-category" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $('#save-category').click(function() {
        var url = "{{ route('invoices.store_file') }}";
        var $btn = $('#save-category');
        var formEl = document.getElementById('createTaskCategory');
        $btn.prop('disabled', true);
        $.easyBlockUI('#createTaskCategory');
        window.apiHttp.postForm(url, formEl).then(function(response) {
            if (response.status == 'success') {
                $(MODAL_LG).modal('hide');
            }
        }).catch(function(err) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    text: err.message,
                    toast: true,
                    position: 'top-end',
                    timer: 4000,
                    showConfirmButton: false
                });
            }
        }).finally(function() {
            $btn.prop('disabled', false);
            $.easyUnblockUI('#createTaskCategory');
        });
    });
    init(MODAL_LG);
</script>
