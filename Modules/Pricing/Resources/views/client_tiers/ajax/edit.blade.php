@php
    $clientDetails = $client->clientDetails;
    $companyName = $clientDetails ? $clientDetails->company_name : '';
    $clientCode = $clientDetails ? $clientDetails->client_code : '';
    $pricingTierId = $clientDetails ? $clientDetails->pricing_tier_id : null;
@endphp
<div class="row">
    <div class="col-sm-12">
        <x-form id="edit-client-tier-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('pricing::app.editPricingTier')
                </h4>
                <div class="row p-20">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="f-14 text-dark-grey mb-12">@lang('app.client')</label>
                            <div class="f-14 font-weight-bold">
                                {{ $client->name }}
                                @if (!empty($companyName))
                                    <span class="text-lightest ml-1">({{ $companyName }})</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="client_code" :fieldLabel="__('pricing::app.customerCode')" fieldName="client_code" :fieldPlaceholder="__('app.code')" :fieldValue="$clientCode" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="pricing_tier_id" :fieldLabel="__('pricing::app.pricingTier')" fieldName="pricing_tier_id" search="true">
                            <option value="">-- @lang('app.none') --</option>
                            @foreach ($tiers as $tier)
                                <option value="{{ $tier->id }}" {{ $pricingTierId == $tier->id ? 'selected' : '' }}>{{ $tier->name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-client-tier" class="mr-3" icon="check" type="button">
                        @lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('pricing.client_tiers.index')" class="border-0">
                        @lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $(document).ready(function() {
        init(RIGHT_MODAL);
    });

    $('body').on('click', '#save-client-tier', function() {
        $.easyAjax({
            url: "{{ route('pricing.client_tiers.update', $client->id) }}",
            container: '#edit-client-tier-form',
            type: "POST",
            blockUI: true,
            data: $('#edit-client-tier-form').serialize(),
            success: function(response) {
                if (response.status == 'success') {
                    if ($(RIGHT_MODAL).hasClass('show')) {
                        document.getElementById('close-task-detail').click();
                        window.LaravelDataTables["client-tiers-table"].draw();
                    } else {
                        window.location.reload();
                    }
                }
            }
        })
    });
</script>
