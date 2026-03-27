<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('payroll::modules.payroll.manageEmployees')</h5>
    <button type="button" onclick="removeOpenModal()" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="manageEmployee" method="POST" class="ajax-form">
            <input type="hidden" name="salary_group_id" value="{{ $salaryGroup->id }}">
            <div class="form-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="my-3">

                            <x-forms.select fieldId="user_id"
                                            :fieldLabel="__('payroll::modules.payroll.assignEmployees')"
                                            fieldName="user_id[]" search="true" fieldRequired="true" multiple="true">
                                @foreach ($employees as $item)
                                    <x-user-option
                                        :user="$item"
                                        :pill="true"
                                        :selected="(in_array($item->id, $selectedEmp))"
                                    />
                                @endforeach
                            </x-forms.select>
                        </div>
                    </div>
                </div>

            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-manage-employee" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $(".select-picker").selectpicker();
    // save channel
    $('#save-manage-employee').click(function () {
        window.apiHttp.postUrlEncoded("{{route('salary_groups.manage_employee')}}", $('#manageEmployee').serialize())
            .then(function (response) {
                if (response.status == "success") {
                    window.location.reload();
                }
            })
            .catch(function (err) {
                $.handleApiFormError(err);
            });
    });
</script>
