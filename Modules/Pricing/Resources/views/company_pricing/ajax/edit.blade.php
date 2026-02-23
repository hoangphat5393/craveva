<div class="row">
    <div class="col-sm-12">
        <x-form id="edit-company-pricing-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.edit') @lang('pricing::app.menu.companyPricing')
                </h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.select fieldId="client_id" :fieldLabel="__('app.client')" fieldName="client_id" search="true">
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" {{ $pricing->client_id == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                    @if(!empty($client->company_name)) 
                                        ({{ $client->company_name }}) 
                                    @endif
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="pricing_tier_id" :fieldLabel="__('pricing::app.pricingTier')" fieldName="pricing_tier_id" search="true">
                            <option value="">-- @lang('app.none') --</option>
                            @foreach ($tiers as $tier)
                                <option value="{{ $tier->id }}" {{ $pricing->pricing_tier_id == $tier->id ? 'selected' : '' }}>
                                    {{ $tier->name }}
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    
                    <div class="col-md-12 mt-3">
                        <h5 class="mb-3">@lang('pricing::app.globalDiscount')</h5>
                    </div>

                    <div class="col-md-6">
                        <x-forms.select fieldId="custom_discount_type" :fieldLabel="__('pricing::app.discountType')" fieldName="custom_discount_type">
                            <option value="">-- @lang('app.none') --</option>
                            <option value="percentage" {{ $pricing->custom_discount_type == 'percentage' ? 'selected' : '' }}>@lang('pricing::app.percentage')</option>
                            <option value="fixed_amount" {{ $pricing->custom_discount_type == 'fixed_amount' ? 'selected' : '' }}>@lang('pricing::app.fixedAmount')</option>
                        </x-forms.select>
                    </div>
                    <div class="col-md-6">
                        <x-forms.number fieldId="custom_discount_value" :fieldLabel="__('pricing::app.discountValue')" fieldName="custom_discount_value" :fieldValue="$pricing->custom_discount_value" />
                    </div>

                    <div class="col-md-6">
                        <x-forms.checkbox :fieldLabel="__('app.active')" fieldName="is_active" fieldId="is_active" fieldValue="1" :checked="$pricing->is_active" />
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="update-company-pricing" class="mr-3" icon="check">
                        @lang('app.update')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('pricing.company_pricing.index')" class="border-0">
                        @lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $('#update-company-pricing').on('click', function(e) {
        e.preventDefault();
        $.easyAjax({
            url: "{{ route('pricing.company_pricing.update', $pricing->id) }}",
            container: '#edit-company-pricing-form',
            type: 'POST',
            blockUI: true,
            data: $('#edit-company-pricing-form').serialize(),
            success: function(response) {
                if (response.status === 'success') {
                    if ($(RIGHT_MODAL).hasClass('show')) {
                        document.getElementById('close-task-detail').click();
                        window.LaravelDataTables["company-pricing-table"].draw();
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
