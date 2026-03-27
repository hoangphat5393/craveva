<div class="row">
    <div class="col-sm-12">
        <x-form id="create-company-pricing-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('pricing::app.addCompanyPricing')
                </h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.select fieldId="client_id" :fieldLabel="__('app.client')" fieldName="client_id" search="true">
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}">
                                    @if (!empty($client->client_code)){{ $client->client_code }} - @endif{{ $client->name }}
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
                                <option value="{{ $tier->id }}">{{ $tier->name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-12 mt-3">
                        <h5 class="mb-3">@lang('pricing::app.globalDiscount')</h5>
                    </div>

                    <div class="col-md-6">
                        <x-forms.select fieldId="custom_discount_type" :fieldLabel="__('pricing::app.discountType')" fieldName="custom_discount_type">
                            <option value="">-- @lang('app.none') --</option>
                            <option value="percentage">@lang('pricing::app.percentage')</option>
                            <option value="fixed_amount">@lang('pricing::app.fixedAmount')</option>
                        </x-forms.select>
                    </div>
                    <div class="col-md-6">
                        <x-forms.number fieldId="custom_discount_value" :fieldLabel="__('pricing::app.discountValue')" fieldName="custom_discount_value" />
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-company-pricing" class="mr-3" icon="check">
                        @lang('app.save')
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
    $('#save-company-pricing').on('click', function(e) {
        e.preventDefault();
        $.easyBlockUI('#create-company-pricing-form');
        window.apiHttp.postUrlEncoded("{{ route('pricing.company_pricing.store') }}", $('#create-company-pricing-form').serialize())
            .then(function(response) {
                if (response.status === 'success') {
                    if ($(RIGHT_MODAL).hasClass('show')) {
                        document.getElementById('close-task-detail').click();
                        window.LaravelDataTables["company-pricing-table"].draw();
                    } else {
                        window.location.href = response.redirectUrl;
                    }
                }
            })
            .catch(function(err) {
                $.handleApiFormError(err);
            })
            .finally(function() {
                $.easyUnblockUI('#create-company-pricing-form');
            });
    });
    $(document).ready(function() {
        init(RIGHT_MODAL);
    });
</script>
