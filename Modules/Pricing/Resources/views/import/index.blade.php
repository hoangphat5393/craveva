<div class="row" id="import_table">
    <div class="col-sm-12">
        <x-form id="import-pricing-data-form">
            <div class="bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">@lang('pricing::app.importPricing')</h4>
                <div class="col-sm-12 pt-2">
                    <div class="alert alert-warning" role="alert">
                        @lang('app.importExcelInfo')
                    </div>
                </div>
                <div class="row py-20">
                    <div class="col-md-6">
                        <x-forms.select fieldId="import_type" :fieldLabel="__('pricing::app.importType')" fieldName="import_type">
                            @if (in_array('admin', user_roles()) || user()->permission('add_client_pricing') == 'all' || user()->permission('add_client_pricing') == 'added')
                                <option value="client_product_pricing">@lang('pricing::app.contractProductPricing')</option>
                            @endif
                            @if (in_array('admin', user_roles()) || user()->permission('add_pricing_tiers') == 'all' || user()->permission('add_pricing_tiers') == 'added')
                                <option value="pricing_tier_items">@lang('pricing::app.pricingTierItems')</option>
                            @endif
                        </x-forms.select>
                    </div>
                    <div class="col-md-6">
                        <x-forms.file :fieldLabel="__('modules.import.file')" fieldName="import_file" fieldId="pricing_import" />
                    </div>
                    <div class="col-md-12">
                        <x-forms.toggle-switch class="mr-0 mr-lg-12" :fieldLabel="__('modules.import.containsHeadings')" fieldName="heading" fieldId="heading" />
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="import-pricing-form" class="mr-3" icon="arrow-right">@lang('app.uploadNext')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('pricing.client_pricing.index')" class="border-0">@lang('app.back')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $("#pricing_import").dropify({
            messages: dropifyMessages
        });

        $('body').on('click', '#import-pricing-form', function() {
            const url = "{{ route('pricing.import.store') }}";

            $.easyBlockUI('#import-pricing-data-form');
            $('#import-pricing-form').prop('disabled', true);
            window.apiHttp.postForm(url, document.getElementById('import-pricing-data-form'))
                .then(function(response) {
                    if (response.status === 'success') {
                        $('#import_table').html(response.view);
                    }
                })
                .catch(function(err) {
                    $.handleApiFormError(err);
                })
                .finally(function() {
                    $.easyUnblockUI('#import-pricing-data-form');
                    $('#import-pricing-form').prop('disabled', false);
                });
        });
    });
</script>
