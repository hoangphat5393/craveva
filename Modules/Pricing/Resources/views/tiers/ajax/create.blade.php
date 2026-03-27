<div class="row">
    <div class="col-sm-12">
        <x-form id="create-pricing-tier-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('pricing::app.addPricingTier')
                </h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.text fieldId="name" :fieldLabel="__('pricing::app.tierName')" fieldName="name" fieldRequired="true" :fieldPlaceholder="__('app.name')" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.number fieldId="priority" :fieldLabel="__('app.priority')" fieldName="priority" :fieldPlaceholder="__('app.priority')" />
                    </div>
                    <div class="col-md-12">
                        <x-forms.textarea fieldId="description" :fieldLabel="__('app.description')" fieldName="description" :fieldPlaceholder="__('app.description')" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.datepicker fieldId="valid_from" :fieldLabel="__('app.startDate')" fieldName="valid_from" :fieldPlaceholder="__('app.startDate')" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.datepicker fieldId="valid_to" :fieldLabel="__('app.endDate')" fieldName="valid_to" :fieldPlaceholder="__('app.endDate')" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="discount_type" :fieldLabel="__('pricing::app.discountType')" fieldName="discount_type">
                            <option value="">-- @lang('app.select') --</option>
                            <option value="percentage">@lang('pricing::app.percentage')</option>
                            <option value="fixed">@lang('pricing::app.fixedAmount')</option>
                        </x-forms.select>
                    </div>
                    <div class="col-md-6">
                        <x-forms.number fieldId="discount_value" :fieldLabel="__('pricing::app.discountValue')" fieldName="discount_value" :fieldPlaceholder="__('pricing::app.discountValue')" />
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-pricing-tier" class="mr-3" icon="check">
                        @lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('pricing.tiers.index')" class="border-0">
                        @lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $('#save-pricing-tier').on('click', function(e) {
        e.preventDefault();
        $.easyBlockUI('#create-pricing-tier-form');
        window.apiHttp.postUrlEncoded("{{ route('pricing.tiers.store') }}", $('#create-pricing-tier-form').serialize())
            .then(function(response) {
                if (response.status === 'success') {
                    if ($(RIGHT_MODAL).hasClass('show')) {
                        document.getElementById('close-task-detail').click();
                        window.LaravelDataTables["pricing-tiers-table"].draw();
                    } else {
                        window.location.href = response.redirectUrl;
                    }
                }
            })
            .catch(function(err) {
                $.handleApiFormError(err);
            })
            .finally(function() {
                $.easyUnblockUI('#create-pricing-tier-form');
            });
    });

    $(document).ready(function() {
        init(RIGHT_MODAL);

        const dp1 = datepicker('#valid_from', {
            position: 'bl',
            ...datepickerConfig
        });

        const dp2 = datepicker('#valid_to', {
            position: 'bl',
            ...datepickerConfig
        });
    });
</script>
