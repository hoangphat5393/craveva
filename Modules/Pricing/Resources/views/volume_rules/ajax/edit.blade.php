<div class="row">
    <div class="col-sm-12">
        <x-form id="edit-volume-rule-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.edit') @lang('pricing::app.volumeRule')
                </h4>
                <div class="row p-20">
                    <div class="col-md-4">
                        <x-forms.text fieldId="name" :fieldLabel="__('pricing::app.ruleName')" fieldName="name" fieldRequired="true"
                                      :fieldPlaceholder="__('app.name')" :fieldValue="$rule->name" />
                    </div>
                    <div class="col-md-4">
                        <x-forms.select fieldId="discount_type" :fieldLabel="__('pricing::app.discountType')" fieldName="discount_type" fieldRequired="true">
                            <option value="percentage" {{ $rule->discount_type === 'percentage' ? 'selected' : '' }}>@lang('pricing::app.percentage')</option>
                            <option value="fixed_amount" {{ $rule->discount_type === 'fixed_amount' ? 'selected' : '' }}>@lang('pricing::app.fixedAmount')</option>
                        </x-forms.select>
                    </div>
                    <div class="col-md-4">
                        <x-forms.text fieldId="discount_value" :fieldLabel="__('pricing::app.discountValue')" fieldName="discount_value" fieldRequired="true" fieldType="number" :fieldValue="$rule->discount_value" />
                    </div>

                    <div class="col-md-4 mt-3">
                        <x-forms.text fieldId="minimum_quantity" :fieldLabel="__('pricing::app.minQuantity')" fieldName="minimum_quantity" fieldRequired="true" fieldType="number" :fieldValue="$rule->minimum_quantity" />
                    </div>
                    <div class="col-md-4 mt-3">
                        <x-forms.text fieldId="maximum_quantity" :fieldLabel="__('pricing::app.maxQuantity')" fieldName="maximum_quantity" fieldType="number" :fieldValue="$rule->maximum_quantity" />
                    </div>
                    <div class="col-md-4 mt-3">
                        <x-forms.select fieldId="applies_to_type" :fieldLabel="__('pricing::app.appliesTo')" fieldName="applies_to_type" fieldRequired="true">
                            <option value="all" {{ $rule->applies_to_type === 'all' ? 'selected' : '' }}>@lang('pricing::app.allProducts')</option>
                            <option value="products" {{ $rule->applies_to_type === 'products' ? 'selected' : '' }}>@lang('pricing::app.specificProducts')</option>
                        </x-forms.select>
                    </div>

                    <div class="col-md-12 mt-3" id="product-select-wrapper" style="display: none;">
                        <x-forms.select fieldId="product_id" :fieldLabel="__('app.product')" fieldName="product_id">
                            <option value="">@lang('app.select') @lang('app.product')</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" {{ $rule->applies_to_product_id == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-3 mt-3">
                        <x-forms.checkbox fieldId="is_active" :fieldLabel="__('app.active')" fieldName="is_active" :fieldValue="'1'" :checked="$rule->is_active" />
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="update-volume-rule" class="mr-3" icon="check">
                        @lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('pricing.volume_rules.index')" class="border-0">
                        @lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    function toggleProductSelect() {
        var type = $('#applies_to_type').val();
        if (type === 'products') {
            $('#product-select-wrapper').show();
        } else {
            $('#product-select-wrapper').hide();
        }
    }

    $('#applies_to_type').on('change', toggleProductSelect);
    toggleProductSelect();

    $('#update-volume-rule').on('click', function(e) {
        e.preventDefault();
        var url = "{{ route('pricing.volume_rules.update', $rule->id) }}";

        $.easyAjax({
            url: url,
            container: '#edit-volume-rule-form',
            type: 'POST',
            blockUI: true,
            data: $('#edit-volume-rule-form').serialize() + '&_method=PUT',
            success: function(response) {
                if (response.status === 'success') {
                    if ($(RIGHT_MODAL).hasClass('show')) {
                        document.getElementById('close-task-detail').click();
                        window.LaravelDataTables["volume-rules-table"].draw();
                    } else {
                        window.location.href = response.redirectUrl;
                    }
                }
            }
        });
    });

    $(document).ready(function() {
        init(RIGHT_MODAL);
    });
</script>
