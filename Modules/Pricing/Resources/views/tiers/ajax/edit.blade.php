<div class="row">
    <div class="col-sm-12">
        <x-form id="save-pricing-tier-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('pricing::app.editPricingTier')
                </h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true" :fieldPlaceholder="__('app.name')" :fieldValue="$pricingTier->name" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.number fieldId="priority" :fieldLabel="__('app.priority')" fieldName="priority" :fieldPlaceholder="__('app.priority')" :fieldValue="$pricingTier->priority" />
                    </div>
                    <div class="col-md-12">
                        <x-forms.text fieldId="description" :fieldLabel="__('app.description')" fieldName="description" :fieldPlaceholder="__('app.description')" :fieldValue="$pricingTier->description" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.datepicker fieldId="valid_from" :fieldLabel="__('app.startDate')" fieldName="valid_from" :fieldPlaceholder="__('app.startDate')" :fieldValue="$pricingTier->valid_from ? \Carbon\Carbon::parse($pricingTier->valid_from)->format(company()->date_format) : ''" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.datepicker fieldId="valid_to" :fieldLabel="__('app.endDate')" fieldName="valid_to" :fieldPlaceholder="__('app.endDate')" :fieldValue="$pricingTier->valid_to ? \Carbon\Carbon::parse($pricingTier->valid_to)->format(company()->date_format) : ''" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="discount_type" :fieldLabel="__('pricing::app.discountType')" fieldName="discount_type">
                            <option value="">-- @lang('app.select') --</option>
                            <option value="percentage" @if($pricingTier->discount_type == 'percentage') selected @endif>@lang('pricing::app.percentage')</option>
                            <option value="fixed" @if($pricingTier->discount_type == 'fixed') selected @endif>@lang('pricing::app.fixedAmount')</option>
                        </x-forms.select>
                    </div>
                    <div class="col-md-6">
                        <x-forms.number fieldId="discount_value" :fieldLabel="__('pricing::app.discountValue')" fieldName="discount_value" :fieldPlaceholder="__('pricing::app.discountValue')" :fieldValue="$pricingTier->discount_value" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="is_active" :fieldLabel="__('app.status')" fieldName="is_active">
                            <option value="1" @if ($pricingTier->is_active) selected @endif>@lang('app.active')</option>
                            <option value="0" @if (!$pricingTier->is_active) selected @endif>@lang('app.inactive')</option>
                        </x-forms.select>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-pricing-tier-form" class="mr-3" icon="check">
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
    $(document).ready(function() {
        $('#save-pricing-tier-form').click(function() {
            const url = "{{ route('pricing.tiers.update', $pricingTier->id) }}";

            $.easyAjax({
                url: url,
                container: '#save-pricing-tier-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-pricing-tier-form",
                data: $('#save-pricing-tier-data-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        if ($(RIGHT_MODAL).hasClass('show')) {
                            document.getElementById('close-task-detail').click();
                            window.LaravelDataTables["pricing-tiers-table"].draw();
                        } else {
                            window.location.href = response.redirectUrl;
                        }
                    }
                }
            });
        });

        init(RIGHT_MODAL);

        const dp1 = datepicker('#valid_from', {
            position: 'bl',
            @if($pricingTier->valid_from)
            dateSelected: new Date("{{ str_replace('-', '/', $pricingTier->valid_from) }}"),
            @endif
            ...datepickerConfig
        });

        const dp2 = datepicker('#valid_to', {
            position: 'bl',
            @if($pricingTier->valid_to)
            dateSelected: new Date("{{ str_replace('-', '/', $pricingTier->valid_to) }}"),
            @endif
            ...datepickerConfig
        });
    });
</script>
