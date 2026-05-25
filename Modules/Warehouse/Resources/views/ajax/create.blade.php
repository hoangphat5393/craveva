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
                            <x-forms.select fieldId="warehouse_type" :fieldLabel="__('warehouse::app.warehouseType')" fieldName="warehouse_type">
                                <option value="normal" @selected(old('warehouse_type', 'normal') === 'normal')>@lang('warehouse::app.warehouseTypeNormal')</option>
                                <option value="locked" @selected(old('warehouse_type') === 'locked')>@lang('warehouse::app.warehouseTypeLocked')</option>
                                <option value="scrap" @selected(old('warehouse_type') === 'scrap')>@lang('warehouse::app.warehouseTypeScrap')</option>
                                <option value="transit" @selected(old('warehouse_type') === 'transit')>@lang('warehouse::app.warehouseTypeTransit')</option>
                            </x-forms.select>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <x-forms.textarea fieldId="address" :fieldLabel="__('warehouse::app.address')" fieldName="address" :fieldValue="old('address')" />
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <x-forms.textarea fieldId="description" :fieldLabel="__('warehouse::app.description')" fieldName="description" :fieldValue="old('description')" />
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <x-forms.select fieldId="status" :fieldLabel="__('warehouse::app.statusLabel')" fieldName="status">
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
    const getReadableApiError = (error, fallbackMessage = @json(__('warehouse::app.unableCreateWarehouse'))) => {
        const err = error?.responseJSON || error?.response?.data || {};
        const errors = err?.errors || {};
        const lines = [];

        Object.keys(errors).forEach((field) => {
            const messages = Array.isArray(errors[field]) ? errors[field] : [errors[field]];
            messages.forEach((msg) => {
                if (msg) lines.push(msg);
            });
        });

        if (lines.length > 0) {
            return lines.join('\n');
        }

        return err?.message || fallbackMessage;
    };

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
                const readableMessage = getReadableApiError(err);
                Swal.fire({
                    icon: 'error',
                    title: @json(__('warehouse::app.validationFailedTitle')),
                    text: readableMessage,
                    timer: 7000,
                    timerProgressBar: true,
                });
            })
            .finally(function() {
                $btn.prop('disabled', false);
                $.easyUnblockUI('#save-warehouse-data-form');
            });
    });
</script>
