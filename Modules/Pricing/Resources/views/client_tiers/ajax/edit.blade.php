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
                                @if (!empty($clientCode))
                                    {{ $clientCode }} -
                                @endif{{ $client->name }}
                                @if (!empty($companyName))
                                    <span class="text-lightest ml-1">({{ $companyName }})</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="client_code_search" :fieldLabel="__('pricing::app.customerCode')" fieldName="client_switcher" search="true">
                            @foreach ($clients as $c)
                                <option value="{{ $c->id }}" data-edit-url="{{ route('pricing.client_tiers.edit', $c->id) }}" @if ($c->id == $client->id) selected @endif>
                                    @if (!empty($c->client_code))
                                        {{ $c->client_code }} -
                                    @endif{{ $c->name }}
                                    @if (!empty($c->company_name))
                                        ({{ $c->company_name }})
                                    @endif
                                </option>
                            @endforeach
                        </x-forms.select>
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

        // Customer Code: chọn client khác -> load form edit (dùng select search như Pricing Tier)
        $('#client_code_search').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var editUrl = selectedOption.data('edit-url');
            if (editUrl && typeof RIGHT_MODAL_CONTENT !== 'undefined') {
                $.easyBlockUI(RIGHT_MODAL);
                window.apiHttp.get(editUrl)
                    .then(function(response) {
                        if (response.status === 'success') {
                            $(RIGHT_MODAL_CONTENT).html(response.html);
                            if (response.title) $(RIGHT_MODAL_TITLE).html(response.title);
                            init(RIGHT_MODAL);
                        }
                    })
                    .catch(function(err) {
                        $.handleApiFormError(err);
                    })
                    .finally(function() {
                        $.easyUnblockUI(RIGHT_MODAL);
                    });
            } else if (editUrl) {
                window.location.href = editUrl;
            }
        });
    });

    $('body').on('click', '#save-client-tier', function() {
        $.easyBlockUI('#edit-client-tier-form');
        window.apiHttp.postUrlEncoded("{{ route('pricing.client_tiers.update', $client->id) }}", $('#edit-client-tier-form').serialize())
            .then(function(response) {
                if (response.status == 'success') {
                    if ($(RIGHT_MODAL).hasClass('show')) {
                        document.getElementById('close-task-detail').click();
                        window.LaravelDataTables["client-tiers-table"].draw();
                    } else {
                        window.location.reload();
                    }
                }
            })
            .catch(function(err) {
                $.handleApiFormError(err);
            })
            .finally(function() {
                $.easyUnblockUI('#edit-client-tier-form');
            })
    });
</script>
