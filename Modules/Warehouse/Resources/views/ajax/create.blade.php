<div class="row">
    <div class="col-sm-12">
        <x-form id="save-warehouse-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                    @lang('warehouse::app.createTitle')
                </h4>

                <div class="p-20">
                    @include('sections.password-autocomplete-hide')
                    <div class="row">
                        <div class="col-lg-6 col-md-6">
                            <x-forms.text fieldId="name" :fieldLabel="__('warehouse::app.name')" fieldName="name" fieldRequired="true" :fieldValue="old('name')" />
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <x-forms.text fieldId="code" :fieldLabel="__('warehouse::app.code')" fieldName="code" :fieldValue="old('code')" />
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <x-forms.textarea fieldId="address" :fieldLabel="__('warehouse::app.address')" fieldName="address" :fieldValue="old('address')" />
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <x-forms.textarea fieldId="description" :fieldLabel="__('warehouse::app.description')" fieldName="description" :fieldValue="old('description')" />
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status">
                                <option value="active" @selected(old('status', 'active') === 'active')>@lang('app.active')</option>
                                <option value="inactive" @selected(old('status') === 'inactive')>@lang('app.inactive')</option>
                            </x-forms.select>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <x-forms.checkbox :fieldLabel="__('warehouse::app.isDefault')" fieldName="is_default" fieldId="is_default" fieldValue="1" :checked="(bool) old('is_default')" />
                        </div>
                    </div>
                </div>

                <div class="w-100 border-top-grey d-flex justify-content-start px-4 py-3">
                    <x-forms.button-primary id="save-warehouse-form" class="mr-3" icon="check">
                        @lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('warehouse.index')" class="border-0">
                        @lang('app.cancel')
                    </x-forms.button-cancel>
                </div>
            </div>
        </x-form>
    </div>
</div>

<script>
    $(function() {
        if (typeof $.fn.selectpicker === 'function') {
            $('.select-picker').selectpicker('refresh');
        }
    });

    $('#save-warehouse-form').click(function() {
        const $btn = $('#save-warehouse-form');
        $btn.prop('disabled', true);
        $.easyBlockUI('#save-warehouse-data-form');
        window.apiHttp.postUrlEncoded("{{ route('warehouse.store') }}", $('#save-warehouse-data-form').serialize())
            .then(function(response) {
                if (response.status === 'success' && response.action === 'redirect') {
                    window.location.href = response.url;
                }
            })
            .catch(function(err) {
                $.handleApiFormError(err);
            })
            .finally(function() {
                $btn.prop('disabled', false);
                $.easyUnblockUI('#save-warehouse-data-form');
            });
    });
</script>
