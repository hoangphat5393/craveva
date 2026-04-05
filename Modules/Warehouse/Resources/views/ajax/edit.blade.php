<div class="row">
    <div class="col-sm-12">
        <x-form id="update-warehouse-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                    @lang('warehouse::app.editTitle')
                </h4>

                <div class="p-20">
                    @include('sections.password-autocomplete-hide')
                    <div class="row">
                        <div class="col-lg-6 col-md-6">
                            <x-forms.text fieldId="name" :fieldLabel="__('warehouse::app.name')" fieldName="name" fieldRequired="true" :fieldValue="old('name', $warehouse->name)" />
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <x-forms.text fieldId="code" :fieldLabel="__('warehouse::app.code')" fieldName="code" :fieldValue="old('code', $warehouse->code)" />
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <x-forms.select fieldId="warehouse_type" :fieldLabel="__('warehouse::app.warehouseType')" fieldName="warehouse_type">
                                <option value="normal" @selected(old('warehouse_type', $warehouse->warehouse_type ?? 'normal') === 'normal')>@lang('warehouse::app.warehouseTypeNormal')</option>
                                <option value="locked" @selected(old('warehouse_type', $warehouse->warehouse_type) === 'locked')>@lang('warehouse::app.warehouseTypeLocked')</option>
                                <option value="scrap" @selected(old('warehouse_type', $warehouse->warehouse_type) === 'scrap')>@lang('warehouse::app.warehouseTypeScrap')</option>
                                <option value="transit" @selected(old('warehouse_type', $warehouse->warehouse_type) === 'transit')>@lang('warehouse::app.warehouseTypeTransit')</option>
                            </x-forms.select>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <x-forms.textarea fieldId="address" :fieldLabel="__('warehouse::app.address')" fieldName="address" :fieldValue="old('address', $warehouse->address)" />
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <x-forms.textarea fieldId="description" :fieldLabel="__('warehouse::app.description')" fieldName="description" :fieldValue="old('description', $warehouse->description)" />
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status">
                                <option value="active" @selected(old('status', $warehouse->status) === 'active')>@lang('app.active')</option>
                                <option value="inactive" @selected(old('status', $warehouse->status) === 'inactive')>@lang('app.inactive')</option>
                            </x-forms.select>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <x-forms.checkbox :fieldLabel="__('warehouse::app.isDefault')" fieldName="is_default" fieldId="is_default" fieldValue="1" :checked="(bool) old('is_default', $warehouse->is_default)" />
                        </div>
                    </div>
                </div>

                <div class="w-100 border-top-grey d-flex justify-content-start px-4 py-3">
                    <x-forms.button-primary id="update-warehouse-form" class="mr-3" icon="check">
                        @lang('app.update')
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

    $('#update-warehouse-form').click(function() {
        const $btn = $('#update-warehouse-form');
        $btn.prop('disabled', true);
        $.easyBlockUI('#update-warehouse-data-form');
        window.apiHttp.postUrlEncoded("{{ route('warehouse.update', $warehouse->id) }}", $('#update-warehouse-data-form').serialize())
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
                $.easyUnblockUI('#update-warehouse-data-form');
            });
    });
</script>
