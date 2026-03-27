<div class="row">
    <div class="col-sm-12">
        <x-form id="create-client-pricing-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('pricing::app.addContractPricing')
                </h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.select fieldId="client_id" :fieldLabel="__('app.client')" fieldName="client_id" search="true">
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}">
                                    @if (!empty($client->client_code)){{ $client->client_code }} - @endif{{ $client->name }}
                                    @if (!empty($client->company_name))
                                        ({{ $client->company_name }})
                                    @endif
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="product_id" :fieldLabel="__('app.product')" fieldName="product_id" search="true">
                            <option value="">-- @lang('app.select') @lang('app.product') --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" data-base-price="{{ $product->price }}">{{ $product->name }}</option>
                            @endforeach
                        </x-forms.select>
                        <div class="invalid-feedback d-none" id="error-product_id">@lang('pricing::app.productRequired')</div>
                    </div>
                    <div class="col-md-4">
                        <x-forms.number fieldId="custom_price" :fieldLabel="__('pricing::app.customPrice')" fieldName="custom_price" :fieldPlaceholder="__('app.price')" />
                        <div class="mt-1">
                            <span id="product-base-price" class="badge badge-light f-14 p-2"></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <x-forms.select fieldId="discount_type" :fieldLabel="__('pricing::app.discountType')" fieldName="discount_type">
                            <option value="">-- @lang('app.none') --</option>
                            <option value="percentage">@lang('pricing::app.percentage')</option>
                            <option value="fixed">@lang('pricing::app.fixedAmount')</option>
                        </x-forms.select>
                    </div>
                    <div class="col-md-4">
                        <x-forms.number fieldId="discount_value" :fieldLabel="__('pricing::app.discountValue')" fieldName="discount_value" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.text :fieldLabel="__('pricing::app.startDate') . ' <span class=\'text-danger\'>*</span>'" fieldName="start_date" fieldId="start_date" :fieldPlaceholder="__('pricing::app.startDate')" />
                        <div class="invalid-feedback d-none" id="error-start_date">@lang('pricing::app.startDateRequired')</div>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text :fieldLabel="__('pricing::app.endDate')" fieldName="end_date" fieldId="end_date" :fieldPlaceholder="__('pricing::app.endDate')" />
                        <div class="invalid-feedback d-none" id="error-end_date">@lang('pricing::app.endDateAfterStartDate')</div>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-client-pricing" class="mr-3" icon="check" disabled>
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
        validateForm();
    });

    function validateForm() {
        let isValid = true;
        
        // 1. Validate Product
        const productId = $('#product_id').val();
        if (!productId) {
            $('#error-product_id').removeClass('d-none').addClass('d-block');
            isValid = false;
        } else {
            $('#error-product_id').removeClass('d-block').addClass('d-none');
        }

        // 2. Validate Start Date
        const startDateVal = $('#start_date').val();
        // We use the datepicker object attached to the element if available, 
        // but since we declared dp1 locally in document.ready, we might not access it easily globally.
        // However, we can re-parse or check the input string if not empty.
        // Better: check if input is empty.
        
        if (!startDateVal) {
             $('#error-start_date').text("@lang('pricing::app.startDateRequired')");
             $('#error-start_date').removeClass('d-none').addClass('d-block');
             isValid = false;
        } else {
            // Check if past
            // Need to parse date based on format. 
            // Since this is complex in pure JS without knowing format map, we rely on the backend for strict check,
            // BUT user asked for client-side check "start date >= today".
            // We can try to use the datepicker instance.
            
            // Let's assume standard datepicker usage.
            // We will do this check inside the datepicker onSelect callback or separate function accessing datepicker.
            // For now, basic empty check. The datepicker config handles invalid formats mostly.
             $('#error-start_date').removeClass('d-block').addClass('d-none');
        }

        // 3. Validate End Date >= Start Date
        const endDateVal = $('#end_date').val();
        if (startDateVal && endDateVal) {
             // We need to compare. 
             // Accessing the date objects from the DOM elements if stored there?
             // Not reliably.
             // We will implement comparison logic in the datepicker setup below.
        }

        // Enable/Disable Button
        // We will manage the button state based on the 'isValid' flag calculated more thoroughly below.
        // Since we need date objects, we'll move the full logic into one function.
    }

    $('#save-client-pricing').on('click', function(e) {
        e.preventDefault();
        
        // Final Validation before submit
        if ($('#save-client-pricing').prop('disabled')) {
            return;
        }

        $.easyBlockUI('#create-client-pricing-form');
        window.apiHttp.postUrlEncoded("{{ route('pricing.client_pricing.store') }}", $('#create-client-pricing-form').serialize())
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
                $.easyUnblockUI('#create-client-pricing-form');
            });
    });

    $(document).ready(function() {
        init(RIGHT_MODAL);
        updateBasePriceLabel();

        const dp1 = datepicker('#start_date', {
            position: 'bl',
            minDate: new Date(), // Prevent selecting past dates
            onSelect: (instance, date) => {
                validateFormState(instance.dateSelected, dp2.dateSelected);
            },
            ...datepickerConfig
        });

        const dp2 = datepicker('#end_date', {
            position: 'bl',
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
                // Check if past (ignoring time)
                const today = new Date();
                today.setHours(0,0,0,0);
                const checkDate = new Date(startDate);
                checkDate.setHours(0,0,0,0);

                if (checkDate < today) {
                     $('#error-start_date').text("@lang('pricing::app.startDateRequired')");
                     $('#error-start_date').removeClass('d-none').addClass('d-block');
                     isValid = false;
                } else {
                     $('#error-start_date').removeClass('d-block').addClass('d-none');
                }
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
            // We need current dates
            // Accessing internal datepicker state via the global variable might be tricky if scope is lost, 
            // but here inside ready it is fine.
            // However, dp1.dateSelected might be null if not selected.
            // But datepicker library (qs-datepicker?) usually exposes it.
            validateFormState(dp1.dateSelected, dp2.dateSelected);
        });
        
        // Input Blur events
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
