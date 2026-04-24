<div class="row">
    <div class="col-sm-12">
        <x-form id="save-transfer-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                    @lang('warehouse::app.transferStock')
                </h4>

                <div class="p-20">
                    <div id="alert"></div>
                    <div class="row">
                        <div class="col-lg-6 col-md-6">
                            <x-forms.select fieldId="warehouse_from_id" :fieldLabel="__('warehouse::app.fromWarehouse')" fieldName="warehouse_from_id" fieldRequired="true" search="true">
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">
                                        {{ $warehouse->name }}{{ $warehouse->code ? ' (' . $warehouse->code . ')' : '' }}{{ $warehouse->is_default ? ' - ' . __('warehouse::app.isDefault') : '' }}
                                    </option>
                                @endforeach
                            </x-forms.select>
                        </div>

                        <div class="col-lg-6 col-md-6">
                            <x-forms.select fieldId="warehouse_to_id" :fieldLabel="__('warehouse::app.toWarehouse')" fieldName="warehouse_to_id" fieldRequired="true" search="true">
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">
                                        {{ $warehouse->name }}{{ $warehouse->code ? ' (' . $warehouse->code . ')' : '' }}{{ $warehouse->is_default ? ' - ' . __('warehouse::app.isDefault') : '' }}
                                    </option>
                                @endforeach
                            </x-forms.select>
                        </div>

                        <div class="col-lg-6 col-md-6">
                            <x-forms.select fieldId="product_id" :fieldLabel="__('warehouse::app.product')" fieldName="product_id" fieldRequired="true" search="true">
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </x-forms.select>
                        </div>

                        <div class="col-lg-6 col-md-6">
                            <x-forms.number fieldId="quantity" :fieldLabel="__('warehouse::app.quantity')" fieldName="quantity" fieldRequired="true" minValue="0.01" step="0.01" :fieldValue="old('quantity')" />
                        </div>

                        <div class="col-lg-12">
                            <x-forms.textarea fieldId="description" :fieldLabel="__('warehouse::app.description')" fieldName="description" :fieldValue="old('description')" />
                        </div>
                    </div>
                </div>

                <div class="w-100 border-top-grey d-flex justify-content-start px-4 py-3">
                    <x-forms.button-primary id="save-transfer-form" class="mr-3" icon="check">@lang('app.save')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('warehouse.stock.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </div>
            </div>
        </x-form>
    </div>
</div>

<script>
    const getReadableApiError = (error, fallbackMessage = 'Unable to save warehouse transfer.') => {
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

    $('#save-transfer-form').click(function() {
        const $btn = $('#save-transfer-form');
        $btn.prop('disabled', true);
        $.easyBlockUI('#save-transfer-data-form');
        window.apiHttp.postUrlEncoded("{{ route('warehouse.transfer.store') }}", $('#save-transfer-data-form').serialize())
            .then(function(response) {
                if (response.status === 'success' && response.action === 'redirect') {
                    window.location.href = response.url;
                }
            })
            .catch(function(err) {
                const readableMessage = getReadableApiError(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Validation failed',
                    text: readableMessage,
                    timer: 7000,
                    timerProgressBar: true,
                });
            })
            .finally(function() {
                $btn.prop('disabled', false);
                $.easyUnblockUI('#save-transfer-data-form');
            });
    });
</script>
