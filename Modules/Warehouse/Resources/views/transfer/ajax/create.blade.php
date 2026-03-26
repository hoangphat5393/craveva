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
                            <x-forms.select fieldId="warehouse_from_id" :fieldLabel="__('warehouse::app.fromWarehouse')" fieldName="warehouse_from_id" fieldRequired="true">
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">
                                        {{ $warehouse->name }}{{ $warehouse->code ? ' (' . $warehouse->code . ')' : '' }}{{ $warehouse->is_default ? ' - ' . __('warehouse::app.isDefault') : '' }}
                                    </option>
                                @endforeach
                            </x-forms.select>
                        </div>

                        <div class="col-lg-6 col-md-6">
                            <x-forms.select fieldId="warehouse_to_id" :fieldLabel="__('warehouse::app.toWarehouse')" fieldName="warehouse_to_id" fieldRequired="true">
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
    $('#save-transfer-form').click(function() {
        $.easyAjax({
            url: "{{ route('warehouse.transfer.store') }}",
            container: '#save-transfer-data-form',
            type: "POST",
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-transfer-form",
            data: $('#save-transfer-data-form').serialize(),
            success: function(response) {
                if (response.status === 'success' && response.action === 'redirect') {
                    window.location.href = response.url;
                }
            }
        });
    });
</script>
