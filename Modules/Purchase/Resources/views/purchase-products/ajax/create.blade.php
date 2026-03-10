@php
    $addProductCategoryPermission = user()->permission('manage_product_category');
    $addProductSubCategoryPermission = user()->permission('manage_product_sub_category');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">
<style>
    .product_type {
        margin-top: 0px !important;
    }

    .track_inventory_label {
        margin-left: 30px !important;
    }

    #purchase_price_div {
        margin-top: 46px !important;
    }

    #salePrice {
        margin-top: 38px !important;
    }
</style>

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-product-form">
            @include('sections.password-autocomplete-hide')

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('app.menu.addProducts')</h4>
                <div class="row p-20">
                    <div class="col-lg-12">
                        <div class="row">

                            <input type="hidden" id="hiddenProductId">
                            <input type="hidden" value="" name="purchase_vendor_id">

                            <div class="col-lg-4 col-md-6">
                                <x-forms.label class="my-3" fieldId="product_type" :fieldLabel="__('purchase::modules.product.type')">
                                </x-forms.label><sup class="text-red f-14 mr-1">*</sup>
                                <div class="form-group">
                                    <div class="d-flex">
                                        <x-forms.radio fieldId="goods_type" class="product_type" :fieldLabel="__('purchase::modules.product.goods')" fieldName="type" fieldValue="goods" :checked="$product && $product->type == 'goods' ? 'true' : 'true'">
                                        </x-forms.radio>

                                        <x-forms.radio class="product_type" fieldId="service_type" :fieldLabel="__('purchase::modules.product.service')" fieldValue="service" fieldName="type" :checked="$product && $product->type == 'service' ? 'true' : ''">
                                        </x-forms.radio>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" :fieldValue="$product ? $product->name : ''" fieldRequired="true" :fieldPlaceholder="__('placeholders.productName')">
                                </x-forms.text>
                            </div>

                            <div class="col-lg-4 col-md-6" id="sku_id">
                                <x-forms.text fieldId="sku" :fieldLabel="__('purchase::app.sku')" fieldName="sku" :fieldValue="$product ? $product->sku : ''" :fieldPlaceholder="__('placeholders.hsnSac')">
                                </x-forms.text>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <x-forms.label class="my-3" fieldId="" :fieldLabel="__('modules.unitType.unitType')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control unit_type select-picker" name="unit_type" id="unit_type_id" data-live-search="true">
                                        @foreach ($unit_types as $unit_type)
                                            <option @if ($product && $unit_type->id == $product->unit_id) selected @elseif ($unit_type->default == 1) selected @endif value="{{ $unit_type->id }}">{{ ucwords($unit_type->unit_type) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </x-forms.input-group>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <x-forms.label class="my-3" fieldId="" :fieldLabel="__('modules.productCategory.productCategory')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="category_id" id="product_category_id" data-live-search="true">
                                        <option value="">--</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" @if ($product && $category->id == $product->category_id) selected @endif>
                                                {{ mb_ucwords($category->category_name) }}</option>
                                        @endforeach
                                    </select>

                                    @if ($addProductCategoryPermission == 'all' || $addProductCategoryPermission == 'added')
                                        <x-slot name="append">
                                            <button id="add-category" type="button" data-toggle="tooltip" data-original-title="{{ __('app.add') . ' ' . __('modules.productCategory.productCategory') }}" class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                        </x-slot>
                                    @endif
                                </x-forms.input-group>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <x-forms.label class="my-3" fieldId="" :fieldLabel="__('modules.productCategory.productSubCategory')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="sub_category_id" id="sub_category_id" data-live-search="true">
                                        <option value="">@lang('messages.noProductSubCategoryAdded')</option>
                                        @foreach ($subCategories as $subCategory)
                                            <option value="{{ $subCategory->id }}" @if ($product && $subCategory->id == $product->sub_category_id) selected @endif>
                                                {{ mb_ucwords($subCategory->category_name) }}</option>
                                        @endforeach
                                    </select>

                                    @if ($addProductSubCategoryPermission == 'all' || $addProductSubCategoryPermission == 'added')
                                        <x-slot name="append">
                                            <button id="add-sub-category" type="button" data-toggle="tooltip" data-original-title="{{ __('app.add') . ' ' . __('modules.productCategory.productSubCategory') }}" class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                        </x-slot>
                                    @endif
                                </x-forms.input-group>
                            </div>

                            <div class="col-md-6">
                                <div class="row mt-5">
                                    <div class="col-md-12">
                                        <x-forms.label class="" fieldId="sales_information" :fieldLabel="__('purchase::app.salesInformation')">
                                        </x-forms.label>
                                    </div>
                                </div>
                                <div class="row" id="salePrice">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="f-14 text-dark-grey mb-12 " for="selling_price">@lang('purchase::app.sellingPrice')<sup class="text-red f-14 mr-1">*</sup></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend  height-35">
                                                    <span class="input-group-text border-grey f-15 bg-additional-grey px-3 text-dark" id="basic-addon1">{{ company()->currency->currency_code }}</span>
                                                </div>
                                                <input type="number" name="selling_price" id="selling_price" class="form-control height-35 f-15 readonly-background" value="{{ $product && $product->price ? $product->price : null }}" placeholder="0" aria-label="0019" aria-describedby="basic-addon1" min="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="col-md-6">
                                <div class="row mt-5">
                                    <div class="col-md-12 purchase-info">
                                        <x-forms.checkbox :fieldLabel="__('purchase::app.purchaseInformation')" fieldName="purchase_information" fieldId="purchase_information" fieldValue="1" fieldRequired="true" :checked='true' />
                                    </div>
                                </div>
                                <div class="row purchase_information" id="purchase_price_div">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="f-14 text-dark-grey mb-12 " for="purchase_price">@lang('purchase::app.costPrice')<sup class="text-red f-14 mr-1">*</sup></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend  height-35 ">
                                                    <span class="input-group-text border-grey f-15 bg-additional-grey px-3 text-dark" id="basic-addon1">{{ company()->currency->currency_code }}</span>
                                                </div>
                                                <input type="number" name="purchase_price" id="purchase_price" class="form-control height-35 f-15 readonly-background" value="{{ $product && $product->purchase_price ? $product->purchase_price : null }}" placeholder="0" aria-label="0019" aria-describedby="basic-addon1" min="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 mt-3">
                                <div class="row">
                                    <div class="col-md-4">
                                        <x-forms.number fieldId="wholesale_price" :fieldLabel="__('Wholesale Price')" fieldName="wholesale_price" :fieldPlaceholder="__('0')">
                                        </x-forms.number>
                                    </div>
                                    <div class="col-md-4">
                                        <x-forms.number fieldId="price_per_box" :fieldLabel="__('Price Per Box')" fieldName="price_per_box" :fieldPlaceholder="__('0')">
                                        </x-forms.number>
                                    </div>
                                    <div class="col-md-4">
                                        <x-forms.number fieldId="employee_price" :fieldLabel="__('Employee Price')" fieldName="employee_price" :fieldPlaceholder="__('0')">
                                        </x-forms.number>
                                    </div>
                                </div>

                            </div>

                            <div class="col-md-4 my-3">
                                <x-forms.label fieldId="" :fieldLabel="__('modules.invoices.tax')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control tax select-picker" name="tax[]" id="tax_id" data-live-search="true" multiple="true">
                                        @foreach ($taxes as $tax)
                                            <option value="{{ $tax->id }}" @if ($product && isset($product->taxes) && array_search($tax->id, json_decode($product->taxes)) !== false) selected @endif>{{ strtoupper($tax->tax_name) }}:
                                                {{ $tax->rate_percent }}%
                                            </option>
                                        @endforeach
                                    </select>

                                    @if (user()->permission('manage_tax') == 'all')
                                        <x-slot name="append">
                                            <button id="add-tax" type="button" data-toggle="tooltip" data-original-title="{{ __('app.add') . ' ' . __('modules.invoices.tax') }}" class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                        </x-slot>
                                    @endif
                                </x-forms.input-group>
                            </div>

                            <div class="col-md-4">
                                <x-forms.text fieldId="hsn_sac_code" :fieldLabel="__('app.hsnSac')" fieldName="hsn_sac_code" :fieldValue="$product ? $product->hsn_sac_code : ''" :fieldPlaceholder="__('placeholders.hsnSac')">
                                </x-forms.text>
                            </div>

                            <div class="col-md-2 mt-5">
                                <x-forms.checkbox class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.purchaseAllow')" fieldName="purchase_allow" fieldId="purchase_allow" fieldValue="no" fieldRequired="true" :checked="$product ? ($product->allow_purchase = 1) : ''" />
                            </div>

                            <div class="col-md-2 mt-5">
                                <x-forms.checkbox class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.downloadable')" fieldName="downloadable" fieldId="downloadable" fieldValue="true" fieldRequired="true" :popover="__('messages.downloadable')" />
                            </div>

                            <div class="col-md-12  mt-2 downloadable d-none">
                                <x-forms.file class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.downloadableFile')" fieldName="downloadable_file" fieldId="downloadable_file" fieldRequired="true" />
                            </div>

                            <div class="col-md-12 mt-4 {{ $product && $product->type == 'service' ? 'd-none' : '' }} track_inventory_div">
                                <x-forms.checkbox class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('purchase::app.trackInventory')" fieldName="track_inventory" fieldId="track_inventory" fieldValue="1" fieldRequired="true" />
                                <label for="" class="track_inventory_label text-dark-grey f-13" id="track_inventory_label">@lang('purchase::messages.trackInventoryMsg')</label>
                            </div>

                            <div class="col-md-12 mt-3 track_inventory d-none">
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-forms.number fieldId="opening_stock" fieldRequired="true" :fieldLabel="__('purchase::app.openingStock')" fieldName="opening_stock" :fieldPlaceholder="__('purchase::placeholders.openingStock')" :popover="__('purchase::app.availableStock')">
                                        </x-forms.number>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 mt-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-forms.select fieldId="storage_condition" :fieldLabel="__('Storage Condition')" fieldName="storage_condition" search="true">
                                            <option value="">--</option>
                                            <option value="Frozen" @if ($product && $product->storage_condition == 'Frozen') selected @endif>Frozen</option>
                                            <option value="Chilled" @if ($product && $product->storage_condition == 'Chilled') selected @endif>Chilled</option>
                                            <option value="Ambient" @if ($product && $product->storage_condition == 'Ambient') selected @endif>Ambient</option>
                                        </x-forms.select>
                                    </div>
                                    <div class="col-md-6">
                                        <x-forms.text fieldId="certification" :fieldLabel="__('Certification')" fieldName="certification" :fieldPlaceholder="__('Certification')">
                                        </x-forms.text>
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-12 mt-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-forms.text fieldId="inventory_type" :fieldLabel="__('Inventory Type')" fieldName="inventory_type" :fieldPlaceholder="__('Inventory Type')">
                                        </x-forms.text>
                                    </div>

                                    <div class="col-md-6">
                                        <x-forms.number fieldId="shelf_life_days" :fieldLabel="__('app.shelfLifeDays')" fieldName="shelf_life_days" :fieldPlaceholder="__('app.shelfLifeDays')" minValue="0">
                                        </x-forms.number>
                                    </div>

                                </div>
                            </div>

                            <div class="col-md-12 mt-3">
                                <div class="form-group">
                                    <x-forms.label class="my-3" fieldId="description-text" :fieldLabel="__('app.description')">
                                    </x-forms.label>
                                    <textarea name="description" id="description-text" rows="4" class="form-control">{{ $product ? $product->description : '' }}</textarea>
                                </div>
                            </div>

                            <div class="col-md-12 mt-3">
                                <x-forms.text fieldId="specification" :fieldLabel="__('Specification (規格)')" fieldName="specification" :fieldPlaceholder="__('Specification')" :fieldValue="$product ? $product->specification : ''">
                                </x-forms.text>
                            </div>

                            <div class="col-lg-12">
                                <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('purchase::modules.product.addImages')" fieldName="file" fieldId="file-upload-dropzones" />
                            </div>
                            <input type ="hidden" name="add_more" value="false" id="add_more" />
                        </div>
                    </div>

                </div>

                <x-forms.custom-field :fields="$fields"></x-forms.custom-field>

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
        //Dropzone class
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
            var ms = 5000;
            ms = 2500;
            setTimeout(function() {
                window.location.href = "{{ route('purchase-products.index') }}"
            }, ms);
        });

        $('#save-product').click(function() {

            const url = "{{ route('purchase-products.store') }}";

            $.easyAjax({
                url: url,
                container: '#save-product-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-product",
                file: true,
                data: $('#save-product-form').serialize(),
                success: function(response) {
                    if (productDropzone.getQueuedFiles().length > 0) {
                        $('#hiddenProductId').val(response.productID);
                        productDropzone.processQueue();
                    } else {
                        window.location.href = response.redirectUrl;
                    }
                }
            });
        });

        $('#save-more-product').click(function() {
            const url = "{{ route('purchase-products.store') }}";
            $('#add_more').val(true);

            $.easyAjax({
                url: url,
                container: '#save-product-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-more-product",
                file: true,
                data: $('#save-product-form').serialize(),
                success: function(response) {
                    if (productDropzone.getQueuedFiles().length > 0) {
                        $('#hiddenProductId').val(response.productID);
                        productDropzone.processQueue();
                    } else {
                        window.location.reload();
                    }
                }
            });
        });

        $(".product_type").click(function() {
            var type = $(this).val();
            if (type == 'service') {
                $('.track_inventory_div').addClass('d-none');
                $('.track_inventory').addClass('d-none');
            } else {
                $('.track_inventory_div').removeClass('d-none');
                if ($('#track_inventory').prop('checked') == true) {
                    $('.track_inventory').removeClass('d-none');
                }
            }
        });

        $('#track_inventory').click(function() {
            if ($(this).prop('checked') == true) {
                $('.track_inventory').removeClass('d-none');
            } else {
                $('.track_inventory').addClass('d-none');
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

            $.easyAjax({
                url: url,
                type: "GET",
                success: function(response) {
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
                }
            })
        });

        $('#add-tax').click(function() {
            const url = "{{ route('taxes.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        init(RIGHT_MODAL);
    });
</script>
