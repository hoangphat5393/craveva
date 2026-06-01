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
</style>

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-product-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                    @lang('app.menu.editProducts') </h4>
                <div class="row p-20">
                    <div class="col-lg-12">
                        <div class="row">

                            <input type="hidden" id="hiddenProductId" value="{{ $product->id }}">

                            @include('purchase::purchase-products.partials.product-form-fields', [
                                'product' => $product,
                                'trackInventory' => $trackInventory ?? null,
                            ])

                            <div class="col-12 purchase-product-form-section">
                                @include('purchase::purchase-products.partials.product-form-section-heading', ['title' => __('purchase::app.productFormSectionMedia')])
                                <div class="row">
                                    <div class="col-lg-12">
                                        <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('purchase::modules.product.addImages')" fieldName="file" fieldId="file-upload-dropzone" />
                                    </div>
                                </div>
                            </div>

                            @if (isset($fields) && count($fields) > 0)
                                <div class="col-12 purchase-product-form-section">
                                    @include('purchase::purchase-products.partials.product-form-section-heading', ['title' => __('purchase::app.productFormSectionAdditionalInfo')])
                                    <x-forms.custom-field :fields="$fields" :model="$productData" :compact="true" class="w-100 m-0"></x-forms.custom-field>
                                </div>
                            @endif

                        </div>
                    </div>

                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-product-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
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

        let lastIndex = 0;
        let defaultImage = '';
        var mockFile = {!! $images !!};

        Dropzone.autoDiscover = false;

        productDropzone = new Dropzone("div#file-upload-dropzone", {
            dictDefaultMessage: "{{ __('app.dragDrop') }}",
            url: "{{ route('product-files.update_images') }}",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            paramName: "file",
            maxFilesize: DROPZONE_MAX_FILESIZE,
            maxFiles: DROPZONE_MAX_FILES,
            autoProcessQueue: false,
            uploadMultiple: true,
            addRemoveLinks: true,
            parallelUploads: DROPZONE_MAX_FILES,
            acceptedFiles: 'image/*',
            init: function() {
                productDropzone = this;
            },
            removedfile: function(file) {
                var index = mockFile.findIndex(x => x.name == file.name);
                mockFile.splice(index, 1);

                if (typeof(file.id) != 'undefined') {
                    Swal.fire({
                        title: "@lang('messages.sweetAlertTitle')",
                        text: "@lang('messages.recoverRecord')",
                        icon: 'warning',
                        showCancelButton: true,
                        focusConfirm: false,
                        confirmButtonText: "@lang('messages.confirmDelete')",
                        cancelButtonText: "@lang('app.cancel')",
                        customClass: {
                            confirmButton: 'btn btn-primary mr-3',
                            cancelButton: 'btn btn-secondary'
                        },
                        showClass: {
                            popup: 'swal2-noanimation',
                            backdrop: 'swal2-noanimation'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            var token = "{{ csrf_token() }}";

                            var url = "{{ route('product-files.destroy', ':id') }}";
                            url = url.replace(':id', file.id);

                            window.apiHttp.delete(url, token)
                                .then(function(response) {
                                    file.previewElement.remove();

                                    if ('{{ $product->default_image }}' == file.hashname) {
                                        let $radio = $('.custom-control-input');
                                        $radio[1].checked = true;
                                    }
                                })
                                .catch(function(err) {
                                    $.handleApiFormError(err);
                                });
                        }
                    });

                    return false;
                }

                file.previewElement.remove();
            }
        });


        productDropzone.on('sending', function(file, xhr, formData) {
            var productID = '{{ $product->id }}';
            formData.append('product_id', productID);
            formData.append('default_image', defaultImage);

            if (mockFile.length > 0) {
                formData.append('uploaded_files', JSON.stringify(mockFile));
            }


            $.easyBlockUI();
        });
        productDropzone.on('uploadprogress', function() {
            $.easyBlockUI();
        });
        productDropzone.on('completemultiple', function() {
            window.location.href = '{{ route('purchase-products.index') }}';
        });

        productDropzone.on('removedfile', function() {
            var grp = $('div#file-upload-dropzone').closest(".form-group");
            var label = $('div#file-upload-box').siblings("label");
            $(grp).removeClass("has-error");
            $(label).removeClass("is-invalid");
        });

        productDropzone.on('error', function(file, message) {
            productDropzone.removeFile(file);
            var grp = $('div#file-upload-dropzone').closest(".form-group");
            var label = $('div#file-upload-box').siblings("label");
            $(grp).find(".help-block").remove();
            var helpBlockContainer = $(grp);

            if (helpBlockContainer.length == 0) {
                helpBlockContainer = $(grp);
            }

            helpBlockContainer.append('<div class="help-block invalid-feedback">' + message + '</div>');
            $(grp).addClass("has-error");
            $(label).addClass("is-invalid");

        });


        productDropzone.on('addedfile', function(file) {
            lastIndex++;

            var div = document.createElement('div');
            div.className = 'form-check-inline custom-control custom-radio mt-2';

            var input = document.createElement('input');
            input.className = 'custom-control-input';
            input.type = 'radio';
            input.name = 'default_image';
            input.id = 'default-image-' + lastIndex;
            input.value = file.hashname != undefined ? file.hashname : file.name;
            if (lastIndex == 1) {
                input.checked = true;
            }
            if ('{{ $product->default_image }}' == file.hashname) {
                input.checked = true;
            }
            div.appendChild(input);

            var label = document.createElement('label');
            label.className = 'custom-control-label pt-1 cursor-pointer';
            label.innerHTML = "@lang('modules.makeDefaultImage')";
            label.htmlFor = 'default-image-' + lastIndex;
            div.appendChild(label);

            file.previewTemplate.appendChild(div);
        });

        mockFile.forEach(file => {
            productDropzone.emit('addedfile', file);
            productDropzone.emit('thumbnail', file, file.file_url);
            productDropzone.files.push(file);
            productDropzone.emit("complete", file);
        });

        productDropzone.options.maxFiles = productDropzone.options.maxFiles - mockFile.length;

        $('#type').on('change changed.bs.select', function() {
            if (typeof window.togglePurchaseProductTypeFields === 'function') {
                window.togglePurchaseProductTypeFields($(this).val());
            }
        });

        if (typeof window.togglePurchaseProductTypeFields === 'function') {
            window.togglePurchaseProductTypeFields($('#type').val());
        }

        if (!$('#purchase_information').prop('checked')) {
            $('.purchase_information').addClass('d-none');
        }

        @include('purchase::purchase-products.partials.product-form-client-validation')

        $('#save-product-form').click(function() {
            window.submitPurchaseProductForm({
                formSelector: '#save-product-data-form',
                url: "{{ route('purchase-products.update', [$product->id]) }}",
                onSuccess: function(response) {
                    if (productDropzone.getQueuedFiles().length > 0) {
                        productID = response.productID
                        defaultImage = response.defaultImage;
                        $('#hiddenProductId').val(productID);
                        productDropzone.processQueue();
                    } else {
                        if ($(MODAL_XL).hasClass('show')) {
                            $(MODAL_XL).modal('hide');
                            window.location.reload();
                        } else {
                            window.location.href = response.redirectUrl;
                        }
                    }
                },
            });
        });

        $('#product_category_id').change(function(e) {
            let categoryId = $(this).val();

            var url = "{{ route('get_product_sub_categories', ':id') }}";
            url = url.replace(':id', categoryId);

            window.apiHttp.get(url)
                .then(function(response) {
                    if (response.status == 'success') {
                        var options = [];
                        var rData = [];
                        rData = response.data;
                        $.each(rData, function(index, value) {
                            var selectData = '';
                            selectData = '<option value="' + value.id + '">' + value
                                .category_name + '</option>';
                            options.push(selectData);
                        });

                        $('#sub_category_id').html('<option value="">--</option>' + options.join(''));
                        $('#sub_category_id').selectpicker('refresh');
                    }
                })
                .catch(function(err) {
                    $.handleApiFormError(err);
                });
        });

        $('#add-category').click(function() {
            const url = "{{ route('productCategory.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        })

        $('#add-sub-category').click(function() {
            const url = "{{ route('productSubCategory.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
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

        $('#downloadable').change(function() {
            if ($(this).is(':checked')) {
                $('.downloadable').removeClass('d-none');
            } else {
                $('.downloadable').addClass('d-none');
            }
        });

        $('#purchase_information').change(function() {
            if ($(this).prop('checked')) {
                $('.purchase_information').removeClass('d-none');
            } else {
                $('.purchase_information').addClass('d-none');
            }
        });

        $('#track_inventory').change(function() {
            if ($(this).is(':checked')) {
                $('.track_inventory').removeClass('d-none');
            } else {
                $('.track_inventory').addClass('d-none');
            }
        });

        var drCustomFieldEvent = $('.custom-field-file .dropify').dropify({
            messages: dropifyMessages
        });
        drCustomFieldEvent.on("dropify.afterClear", function(event, element) {
            var elementName = element.element.name;
            $('input[type=hidden][name="' + elementName + '"]').val('');
        });

        datepicker('#expiry_date', {
            position: 'bl',
            ...datepickerConfig
        });

        init(RIGHT_MODAL);
    });
</script>
