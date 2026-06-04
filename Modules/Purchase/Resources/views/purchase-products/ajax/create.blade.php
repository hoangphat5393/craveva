@php
    $addProductCategoryPermission = user()->permission('manage_product_category');
    $addProductSubCategoryPermission = user()->permission('manage_product_sub_category');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">
<style>
    .product_type {
        margin-top: 0 !important;
    }

    .track_inventory_label {
        margin-left: 0 !important;
    }

    .purchase-product-form-section+.purchase-product-form-section {
        margin-top: 1.25rem;
        padding-top: 0.25rem;
    }

    .purchase-product-form-section__title {
        margin-bottom: 1rem !important;
    }

    .product-b2b-collapse-toggle {
        margin-top: 0.35rem;
        margin-bottom: 1.5rem;
    }

    .purchase-product-form-section.product-form-section-tax--accordion {
        margin-top: 1.75rem !important;
        padding-top: 0.35rem;
    }

    .product-form-section-tax .product-tax-accordion-toggle {
        margin-bottom: 0.75rem;
    }
</style>

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-product-form">
            @include('sections.password-autocomplete-hide')

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                    @lang('app.menu.addProducts')</h4>
                <div class="row p-20">
                    <div class="col-lg-12">
                        <div class="row">

                            <input type="hidden" id="hiddenProductId">
                            <input type="hidden" value="" name="purchase_vendor_id">

                            @include('purchase::purchase-products.partials.product-form-fields', [
                                'product' => $product ?? null,
                            ])

                            <div class="col-12 purchase-product-form-section product-media-section">
                                @include('purchase::purchase-products.partials.product-form-section-heading', ['title' => __('purchase::app.productFormSectionMedia')])
                                <div class="row">
                                    <div class="col-lg-12">
                                        <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('purchase::modules.product.addImages')" fieldName="file" fieldId="file-upload-dropzones" />
                                    </div>
                                </div>
                            </div>

                            @if (isset($fields) && count($fields) > 0)
                                <div class="col-12 purchase-product-form-section">
                                    @include('purchase::purchase-products.partials.product-form-section-heading', ['title' => __('purchase::app.productFormSectionAdditionalInfo')])
                                    <x-forms.custom-field :fields="$fields" :compact="true" class="w-100 m-0"></x-forms.custom-field>
                                </div>
                            @endif

                            <input type="hidden" name="add_more" value="false" id="add_more" />
                        </div>
                    </div>

                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-product" class="mr-3 px-0" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-secondary class="mr-3" id="save-more-product" icon="check-double">@lang('app.saveAddMore')
                    </x-forms.button-secondary>
                    <x-forms.button-cancel :link="route('purchase-products.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function() {

        $('.unit_type, .tax').selectpicker();

        let defaultImage = '';
        let lastIndex = 0;

        Dropzone.autoDiscover = false;
        productDropzone = new Dropzone("div#file-upload-dropzones", {
            dictDefaultMessage: "{{ __('app.dragDrop') }}",
            url: "{{ route('product-files.store') }}",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            paramName: "file",
            maxFilesize: DROPZONE_MAX_FILESIZE,
            maxFiles: 10,
            autoProcessQueue: false,
            uploadMultiple: true,
            addRemoveLinks: true,
            parallelUploads: 10,
            acceptedFiles: 'image/*',
            init: function() {
                productDropzone = this;
            }
        });
        productDropzone.on('sending', function(file, xhr, formData) {
            formData.append('product_id', $('#hiddenProductId').val());
            $.each(productDropzone.files, function(index, file) {
                formData.append('images[]', file);
            });
            formData.append('add_more', $('#add_more').val());
        });
        productDropzone.on('completemultiple', function() {
            var ms = 2500;
            setTimeout(function() {
                window.location.href = "{{ route('purchase-products.index') }}"
            }, ms);
        });

        @include('purchase::purchase-products.partials.product-form-client-validation')

        $('#save-product').click(function() {
            $('#add_more').val(false);
            window.submitPurchaseProductForm({
                formSelector: '#save-product-form',
                url: "{{ route('purchase-products.store') }}",
                onSuccess: function(response) {
                    if (productDropzone.getQueuedFiles().length > 0) {
                        $('#hiddenProductId').val(response.productID);
                        productDropzone.processQueue();
                    } else {
                        window.location.href = response.redirectUrl;
                    }
                },
            });
        });

        $('#save-more-product').click(function() {
            $('#add_more').val(true);
            window.submitPurchaseProductForm({
                formSelector: '#save-product-form',
                url: "{{ route('purchase-products.store') }}",
                onSuccess: function(response) {
                    if (productDropzone.getQueuedFiles().length > 0) {
                        $('#hiddenProductId').val(response.productID);
                        productDropzone.processQueue();
                    } else {
                        window.location.reload();
                    }
                },
            });
        });

        $('#type').on('change changed.bs.select', function() {
            if (typeof window.togglePurchaseProductTypeFields === 'function') {
                window.togglePurchaseProductTypeFields($(this).val());
            }
        });

        if (typeof window.togglePurchaseProductTypeFields === 'function') {
            window.togglePurchaseProductTypeFields($('#type').val());
        }

        if (!$('#purchase_information').prop('checked')) {
            $('.product-cost-price-column').addClass('d-none');
        }

        $('#track_inventory').click(function() {
            if ($(this).prop('checked') == true) {
                $('.track_inventory').removeClass('d-none');
            } else {
                $('.track_inventory').addClass('d-none');
            }
        });

        $('#purchase_information').change(function() {
            if ($(this).prop('checked')) {
                $('.product-cost-price-column').removeClass('d-none');
            } else {
                $('.product-cost-price-column').addClass('d-none');
            }
        });

        $('#add-category').click(function() {
            const url = "{{ route('productCategory.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#add-sub-category').click(function() {
            let catID = $('#product_category_id').val();
            const url = "{{ route('productSubCategory.create') }}?catID=" + catID;
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#product_category_id').change(function(e) {
            let categoryId = $(this).val();
            let url = "{{ route('get_product_sub_categories', ':id') }}";
            url = (categoryId) ? url.replace(':id', categoryId) : url.replace(':id', null);

            window.apiHttp.get(url)
                .then(function(response) {
                    if (response.status == 'success') {
                        var options = [];
                        var rData = response.data;
                        $.each(rData, function(index, value) {
                            var selectData = '<option value="' + value.id + '">' + value.category_name + '</option>';
                            options.push(selectData);
                        });

                        var defaultOption = '<option value="">@lang('messages.noProductSubCategoryAdded')</option>';
                        if (options.length > 0) {
                            defaultOption = '<option value="">--</option>';
                        }

                        $('#sub_category_id').html(defaultOption + options.join(''));
                        $('#sub_category_id').selectpicker('refresh');
                    }
                })
                .catch(function(err) {
                    $.handleApiFormError(err);
                });
        });

        $('#add-tax').click(function() {
            const url = "{{ route('taxes.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#add-unit-type').click(function() {
            const url = "{{ route('unit-type.create') }}?no_reload=1";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        datepicker('#expiry_date', {
            position: 'bl',
            ...datepickerConfig
        });

        init(RIGHT_MODAL);
    });
</script>
