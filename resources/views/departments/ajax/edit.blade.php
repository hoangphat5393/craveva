<style>
    .mt{
        margin-top: -4px;
    }
</style>

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-department-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('app.menu.editDepartment')</h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.text fieldId="team_name" :fieldLabel="__('app.name')" fieldName="team_name"
                            fieldRequired="true" fieldValue="{{ $department->team_name }}">
                        </x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.label class="my-3 mt-2" fieldId="parent_label" :fieldLabel="__('app.parentId')" fieldName="parent_label">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker mt" name="parent_id" id="parent_id"
                                data-live-search="true">
                                <option value="">--</option>
                                @foreach($departments as $item)
                                        <option value="{{ $item->id }}" @if($department->parent_id == $item->id) selected @endif>{{ $item->team_name }}</option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-department-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('departments.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function() {

        $('#save-department-form').click(function() {

            const url = "{{ route('departments.update', $department->id) }}";
            const button = $('#save-department-form');
            const buttonText = button.html();

            button.prop('disabled', true);
            $.easyBlockUI('#save-department-data-form');

            window.apiHttp.postUrlEncoded(url, $('#save-department-data-form').serialize())
                .then((response) => {
                    window.location.href = response.redirectUrl;
                })
                .catch((error) => {
                    if (typeof $.handleApiFormError === 'function') {
                        $.handleApiFormError(error);
                    }
                })
                .finally(() => {
                    button.prop('disabled', false);
                    button.html(buttonText);
                    $.easyUnblockUI('#save-department-data-form');
                });
        });

        init(RIGHT_MODAL);
    });
</script>
