<div class="modal-header">
    <h5 class="modal-title">@lang('modules.customFields.addField')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="createForm" method="POST" class="ajax-form">
            <div class="row">

                <div class="col-lg-6">
                    <x-forms.select fieldId="module" :fieldLabel="__('app.module')" fieldName="module" search="true">
                        @foreach ($customFieldGroups as $item)
                            <option value="{{ $item->id }}">{{ \App\Models\CustomFieldGroup::settingsModuleLabel($item->name, $item->model) }}</option>
                        @endforeach
                    </x-forms.select>
                </div>

                <div class="col-lg-6">
                    <x-forms.text :fieldLabel="__('modules.customFields.label')" fieldName="label" fieldId="label" fieldRequired="true" />
                </div>

                <div class="col-lg-6">
                    <div class="form-group my-3">
                        <label class="f-14 text-dark-grey mb-12 w-100" for="usr">@lang('app.required')</label>
                        <div class="d-flex">
                            <x-forms.radio fieldId="optionsRadios1" :fieldLabel="__('app.yes')" fieldName="required" fieldValue="yes" checked="true">
                            </x-forms.radio>
                            <x-forms.radio fieldId="optionsRadios2" :fieldLabel="__('app.no')" fieldValue="no" fieldName="required"></x-forms.radio>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <x-forms.select fieldId="type" :fieldLabel="__('modules.customFields.fieldType')" fieldName="type" search="true">
                        @foreach ($types as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </x-forms.select>
                </div>

                <div class="col-lg-6">
                    <div class="form-group my-5">
                        <x-forms.checkbox fieldId="export" :fieldLabel="__('modules.customFields.export')" fieldName="export" fieldValue="1" />
                    </div>
                </div>

            </div>
            <div class="form-group mt-repeater d-none">
                <div id="addMoreBox1" class="row my-3">
                    <div class="col-md-10">
                        <div class="form-group">
                            <label class="control-label">@lang('app.value')</label>
                            <input class="form-control height-35 f-14" name="value[]" type="text" value="" placeholder="" />
                        </div>
                    </div>
                </div>
                <div id="insertBefore"></div>
                <div class="row">
                    <div class="col-md-12 mt-4">

                        <a class="f-15 f-w-500" href="javascript:;" data-repeater-create id="plusButton"><i class="icons icon-plus font-weight-bold mr-1"></i>@lang('modules.invoices.addItem')</a>
                    </div>
                </div>
            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-custom-field" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $(".select-picker").selectpicker();

    var $insertBefore = $('#insertBefore');
    var $i = 1;

    // Add More Inputs
    $('#plusButton').click(function() {
        $i = $i + 1;
        var indexs = $i + 1;
        $('<div id="addMoreBox' + indexs +
            '" class="row my-3"> <div class="col-md-10">  <label class="control-label">@lang('app.value')</label> <input class="form-control height-35 f-14" name="value[]" type="text" value="" placeholder=""/>  </div> <div class="col-md-1"> <div class="task_view mt-4"> <a href="javascript:;" class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" onclick="removeBox(' +
            indexs + ')"> <i class="fa fa-trash icons mr-2"></i> @lang('app.delete')</a> </div> </div></div>').insertBefore($insertBefore);
    });

    // Remove fields
    function removeBox(index) {
        $('#addMoreBox' + index).remove();
    }

    $('#type').on('change', function() {
        (this.value === 'select' || this.value === 'radio' || this.value === 'checkbox') ? $('.mt-repeater').removeClass('d-none'): $('.mt-repeater').addClass('d-none');
    });

    $('#save-custom-field').click(function() {
        const url = "{{ route('superadmin.settings.global-custom-fields.store') }}";
        const $btn = $('#save-custom-field');
        const prev = $btn.html();
        $.easyBlockUI('#createForm');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + (document.loading || 'Loading...'));
        window.apiHttp.postForm(url, document.getElementById('createForm'))
            .then(function(response) {
                if (response.status === 'success') {
                    window.location.reload();
                }
            })
            .catch(function(err) {
                $.handleApiFormError(err);
            })
            .finally(function() {
                $.easyUnblockUI('#createForm');
                $btn.prop('disabled', false).html(prev);
            });
        return false;
    })
</script>
