<div class="row">
    <div class="col-sm-12">
        <x-form id="edit-client-pricing-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.edit') @lang('pricing::app.menu.contractPricing')
                </h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.select fieldId="client_id" :fieldLabel="__('app.client')" fieldName="client_id" search="true">
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" @if ($client->id == $pricing->client_id) selected @endif>
                                    @if (!empty($client->client_code))
                                        {{ $client->client_code }} -
                                    @endif{{ $client->name }}
                                    @if (!empty($client->company_name))
                                        ({{ $client->company_name }})
                                    @endif
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="product_id" :fieldLabel="__('app.product')" fieldName="product_id" search="true">
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" data-base-price="{{ $product->price }}" @if ($product->id == $pricing->product_id) selected @endif>
                                    {{ $product->name }}{{ !empty($product->sku) ? ' (' . $product->sku . ')' : '' }}
                                </option>
                            @endforeach
                        </x-forms.select>
                        <div class="invalid-feedback d-none" id="error-product_id">@lang('pricing::app.productRequired')</div>
                    </div>
                    <div class="col-md-4">
                        <x-forms.number fieldId="custom_price" :fieldLabel="__('pricing::app.customPrice')" fieldName="custom_price" :fieldPlaceholder="__('app.price')" :fieldValue="$pricing->custom_price" />
                        <div class="mt-1">
                            <span id="product-base-price" class="badge badge-light f-14 p-2"></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <x-forms.select fieldId="discount_type" :fieldLabel="__('pricing::app.discountType')" fieldName="discount_type">
                            <option value="">-- @lang('app.none') --</option>
                            <option value="percentage" @if ($pricing->discount_type == 'percentage') selected @endif>@lang('pricing::app.percentage')</option>
                            <option value="fixed" @if ($pricing->discount_type == 'fixed') selected @endif>@lang('pricing::app.fixedAmount')</option>
                        </x-forms.select>
                    </div>
                    <div class="col-md-4">
                        <x-forms.number fieldId="discount_value" :fieldLabel="__('pricing::app.discountValue')" fieldName="discount_value" :fieldValue="$pricing->discount_value" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.text :fieldLabel="__('pricing::app.startDate') . ' <span class=\'text-danger\'>*</span>'" fieldName="start_date" fieldId="start_date" :fieldPlaceholder="__('pricing::app.startDate')" :fieldValue="$pricing->start_date ? $pricing->start_date->format(company()->date_format) : ''" />
                        <div class="invalid-feedback d-none" id="error-start_date">@lang('pricing::app.startDateRequired')</div>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text :fieldLabel="__('pricing::app.endDate')" fieldName="end_date" fieldId="end_date" :fieldPlaceholder="__('pricing::app.endDate')" :fieldValue="$pricing->end_date ? $pricing->end_date->format(company()->date_format) : ''" />
                        <div class="invalid-feedback d-none" id="error-end_date">@lang('pricing::app.endDateAfterStartDate')</div>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-client-pricing" class="mr-3" icon="check">
                        @lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('pricing.client_pricing.index')" class="border-0">
                        @lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    function updateBasePriceLabel() {
        var $selected = $('#product_id').find('option:selected');
        var basePrice = $selected.data('base-price');

        if (typeof basePrice !== 'undefined') {
            $('#product-base-price').text('Base price: ' + basePrice);
        } else {
            $('#product-base-price').text('');
        }
    }

    $('#product_id').on('change', function() {
        updateBasePriceLabel();
    });

    $('#save-client-pricing').on('click', function(e) {
        e.preventDefault();

        if ($('#save-client-pricing').prop('disabled')) {
            return;
        }

        var url = "{{ route('pricing.client_pricing.update', $pricing->id) }}";

        $.easyBlockUI('#edit-client-pricing-form');
        window.apiHttp.postUrlEncoded(url, $('#edit-client-pricing-form').serialize())
            .then(function(response) {
                if (response.status === 'success') {
                    if ($(RIGHT_MODAL).hasClass('show')) {
                        document.getElementById('close-task-detail').click();
                        window.LaravelDataTables["client-pricing-table"].draw();
                    } else {
                        window.location.href = response.redirectUrl;
                    }
                }
            })
            .catch(function(err) {
                $.handleApiFormError(err);
            })
            .finally(function() {
                $.easyUnblockUI('#edit-client-pricing-form');
            });
    });

    $(document).ready(function() {
        init(RIGHT_MODAL);
        updateBasePriceLabel();

        const dp1 = datepicker('#start_date', {
            position: 'bl',
            @if ($pricing->start_date)
                dateSelected: new Date("{{ $pricing->start_date }}"),
            @endif
            @if ($pricing->start_date && $pricing->start_date->isPast())
                minDate: new Date("{{ $pricing->start_date }}"),
            @else
                minDate: new Date(),
            @endif
            onSelect: (instance, date) => {
                validateFormState(instance.dateSelected, dp2.dateSelected);
            },
            ...datepickerConfig
        });

        const dp2 = datepicker('#end_date', {
            position: 'bl',
            @if ($pricing->end_date)
                dateSelected: new Date("{{ $pricing->end_date }}"),
            @endif
            onSelect: (instance, date) => {
                validateFormState(dp1.dateSelected, instance.dateSelected);
            },
            ...datepickerConfig
        });

        function validateFormState(startDate, endDate) {
            let isValid = true;

            // Product
            if (!$('#product_id').val()) {
                $('#error-product_id').removeClass('d-none').addClass('d-block');
                isValid = false;
            } else {
                $('#error-product_id').removeClass('d-block').addClass('d-none');
            }

            // Start Date
            if (!startDate) {
                $('#error-start_date').text("@lang('pricing::app.startDateRequired')");
                $('#error-start_date').removeClass('d-none').addClass('d-block');
                isValid = false;
            } else {
                // Check if past (ignoring time) - SKIP strict check for edit if it matches original?
                // The minDate in datepicker already handles user selection.
                // We just check if it's empty.
                $('#error-start_date').removeClass('d-block').addClass('d-none');
            }

            // End Date
            if (endDate) {
                if (startDate && endDate < startDate) {
                    $('#error-end_date').removeClass('d-none').addClass('d-block');
                    isValid = false;
                } else {
                    $('#error-end_date').removeClass('d-block').addClass('d-none');
                }
            } else {
                $('#error-end_date').removeClass('d-block').addClass('d-none');
            }

            $('#save-client-pricing').prop('disabled', !isValid);
        }

        // Bind events
        $('#product_id').on('change', function() {
            validateFormState(dp1.dateSelected, dp2.dateSelected);
        });

        $('#start_date').on('blur', function() {
            validateFormState(dp1.dateSelected, dp2.dateSelected);
        });
        $('#end_date').on('blur', function() {
            validateFormState(dp1.dateSelected, dp2.dateSelected);
        });

        // Initial check
        validateFormState(dp1.dateSelected, dp2.dateSelected);
    });
</script>
