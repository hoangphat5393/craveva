<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('app.edit') @lang('payroll::modules.payroll.salaryGroup')</h5>
    <button type="button" onclick="removeOpenModal()" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="editSalaryGroup" method="PUT" class="ajax-form">
            <div class="form-body">
                <div class="row">
                    <div class="col-lg-6">
                        <x-forms.text fieldId="group_name" :fieldLabel="__('app.name')" fieldName="group_name" fieldRequired="true" :fieldValue="$salaryGroup->group_name">
                        </x-forms.text>
                    </div>
                    <div class="col-lg-6">
                        <x-forms.select fieldId="salary_components" :fieldLabel="__('payroll::modules.payroll.assignComponents')" fieldName="salary_components[]" search="true" fieldRequired="true" multiple="true">
                            @foreach ($salaryComponents as $salaryComponent)
                                <option value="{{ $salaryComponent->id }}" @foreach ($salaryGroup->components as $item)
                                        @if ($item->salary_component_id == $salaryComponent->id)
                                        selected
                                    @endif @endforeach>{{ $salaryComponent->component_name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                </div>

            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-salary-group" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $(".select-picker").selectpicker();
    // save channel
    $('#save-salary-group').click(function() {
        window.apiHttp.postUrlEncoded("{{ route('salary-groups.update', $salaryGroup->id) }}", $('#editSalaryGroup').serialize())
            .then(function(response) {
                if (response.status == "success") {
                    window.location.reload();
                }
            })
            .catch(function(err) {
                $.handleApiFormError(err);
            });
    });
</script>
