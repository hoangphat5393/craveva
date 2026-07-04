<x-form id="add-lead-category" method="POST" class="ajax-form">
    <div class="modal-header">
        <h5 class="modal-title" id="modelHeading">@lang('app.addNewDealCategory')</h5>
        <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
                aria-hidden="true">×</span></button>
    </div>
    <div class="modal-body">
        <div class="portlet-body">
                <div class="form-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <x-forms.text fieldId="category_name" :fieldLabel="__('modules.projectCategory.categoryName')"
                                fieldName="category_name" fieldRequired="true">
                            </x-forms.text>
                        </div>
                    </div>
                </div>
        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
        <x-forms.button-primary id="save-source" icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</x-form>

<script>
    // save source
    $('#save-source').click(function() {
        $('#save-source').prop('disabled', true);
        $.easyBlockUI('#add-lead-category');

        window.apiHttp.postUrlEncoded("{{ route('leadCategory.store') }}", $('#add-lead-category').serialize())
            .then(function(response) {
                if (response.status == "success") {
                    if($('table#example').length) {
                        window.location.reload();
                    }
                    else {
                        $('#category_id').html(response.data);
                        $('#category_id').selectpicker('refresh');
                        $('#deal_agent_id').html('<option value="">--</option>');
                        $('#deal_agent_id').selectpicker('refresh');
                        $(MODAL_LG).modal('hide');
                    }
                }
            })
            .catch(function(err) {
                $.handleApiFormError(err);
            })
            .finally(function() {
                $('#save-source').prop('disabled', false);
                $.easyUnblockUI('#add-lead-category');
            });
    });
</script>
