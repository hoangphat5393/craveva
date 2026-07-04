<div class="row">
    <div class="col-sm-12">
        <x-form id="save-designation-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('app.menu.addDesignation')</h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.text fieldId="designation_name" :fieldLabel="__('app.name')" fieldName="name"
                                      fieldRequired="true" :fieldPlaceholder="__('placeholders.designation')">
                        </x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('app.menu.parent_id')"
                                       fieldName="parent_label">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="parent_id" id="parent_id"
                                    data-live-search="true">
                                <option value="">--</option>
                                @foreach($designations as $designation)
                                    <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-designation-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('designations.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function () {

        $('#save-designation-form').click(function () {

            const url = "{{ route('designations.store') }}";
            const button = $('#save-designation-form');
            const buttonText = button.html();

            button.prop('disabled', true);
            $.easyBlockUI('#save-designation-data-form');

            window.apiHttp.postUrlEncoded(url, $('#save-designation-data-form').serialize())
                .then((response) => {
                    if (response.status === 'success') {
                        if ($(MODAL_XL).hasClass('show')) {
                            $(MODAL_XL).modal('hide');
                            window.location.reload();
                        } else {
                            window.location.href = response.redirectUrl;
                        }
                    }
                })
                .catch((error) => {
                    if (typeof $.handleApiFormError === 'function') {
                        $.handleApiFormError(error);
                    }
                })
                .finally(() => {
                    button.prop('disabled', false);
                    button.html(buttonText);
                    $.easyUnblockUI('#save-designation-data-form');
                });
        });

        init(RIGHT_MODAL);
    });
</script>
