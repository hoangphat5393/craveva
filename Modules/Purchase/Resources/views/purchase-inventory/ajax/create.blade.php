<div class="row">
    <div class="col-sm-12">
        <x-form id="save-inventory-form">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('purchase::app.addInventory')</h4>
                <div class="row p-20">
                    <div class="col-lg-12">

                        <div class="row">
                            <div class="col-md-3">
                                <x-forms.label class="my-3" fieldId="" :fieldLabel="__('purchase::modules.product.modeOfAdjustment')">
                                </x-forms.label><sup class="text-red f-14 mr-1">*</sup>
                                <div class="form-group">
                                    <div class="d-flex">
                                        <x-forms.radio class="quantity mode_of_adjustment" fieldId="quantity" :fieldLabel="__('purchase::modules.product.quantityAdjustment')" fieldValue="quantity" fieldName="type" :checked="true"></x-forms.radio>

                                        <x-forms.radio class="value mode_of_adjustment" fieldId="value" :fieldLabel="__('purchase::modules.product.valueAdjustment')" fieldValue="value" fieldName="type"></x-forms.radio>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-4">
                                <x-forms.text fieldId="reference_number" :fieldLabel="__('purchase::modules.product.referenceNumber')" fieldName="reference_number">
                                </x-forms.text>
                            </div>

                            <div class="col-md-4">
                                <x-forms.text :fieldLabel="__('app.date')" fieldName="date" fieldId="date" :fieldPlaceholder="__('app.date')" :fieldValue="now(company()->timezone)->translatedFormat(company()->date_format)" fieldRequired />
                            </div>


                            <div class="col-md-4">
                                <x-forms.label class="mt-3" fieldId="warehouse_id" :fieldLabel="__('purchase::modules.inventory.warehouse')" fieldRequired>
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="warehouse_id" id="warehouse_id" data-live-search="true" @if ($warehouses->count()) required @endif>
                                        <option value="">--</option>
                                        @foreach ($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}">
                                                {{ mb_ucwords($warehouse->name) }}{{ $warehouse->code ? ' (' . $warehouse->code . ')' : '' }}{{ $warehouse->is_default ? ' - ' . __('warehouse::app.isDefault') : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </x-forms.input-group>
                            </div>


                            <div class="col-md-4">
                                <x-forms.label class="mt-3" fieldId="reason" :fieldLabel="__('purchase::modules.product.reason')" fieldRequired>
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="reason_id" id="adjustment_reason_id" data-live-search="true">
                                        <option value="">--</option>
                                        @foreach ($reasons as $reason)
                                            <option value="{{ $reason->id }}">
                                                {{ mb_ucwords($reason->name) }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <x-slot name="append">
                                        <button id="addReason" type="button" class="btn btn-outline-secondary border-grey" data-toggle="tooltip" data-original-title="{{ __('purchase::modules.inventory.addReason') }}">@lang('app.add')</button>
                                    </x-slot>
                                </x-forms.input-group>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <x-forms.label class="my-3" fieldId="description-text" :fieldLabel="__('app.description')">
                                    </x-forms.label>
                                    <textarea name="description" id="description-text" rows="4" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>

                        @if (isset($fields) && count($fields) > 0)
                            <div class="row mt-3">
                                <x-forms.custom-field :fields="$fields"></x-forms.custom-field>
                            </div>
                        @endif

                        <div class="row mt-3">
                            <div class="col-md-3 d-none product-category-filter">
                                <div class="form-group c-inv-select mb-4">
                                    <x-forms.input-group>
                                        <select class="form-control select-picker" name="category_id" id="product_category_id" data-live-search="true">
                                            <option value="">
                                                {{ __('app.select') . ' ' . __('app.product') . ' ' . __('app.category') }}
                                            </option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">
                                                    {{ $category->category_name }}</option>
                                            @endforeach
                                        </select>
                                    </x-forms.input-group>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group c-inv-select mb-4">
                                    <x-forms.input-group>
                                        <select class="form-control select-picker" data-live-search="true" data-size="8" id="add-products">
                                            <option value="">{{ __('app.select') . ' ' . __('app.product') }}
                                            </option>
                                            @foreach ($products as $item)
                                                <option data-content="{{ $item->name }}" value="{{ $item->id }}">
                                                    {{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                        <x-slot name="preappend">
                                            <a href="javascript:;" class="btn btn-outline-secondary border-grey toggle-product-category" data-toggle="tooltip" data-original-title="{{ __('modules.productCategory.filterByCategory') }}"><i class="fa fa-filter"></i></a>
                                        </x-slot>
                                    </x-forms.input-group>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <x-alert id="alertMessage" type="danger">@lang('messages.addItem')</x-alert>
                            </div>
                        </div>

                        <div id="sortable">
                        </div>

                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary class="mr-3 save-form" icon="check" data-type="save">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-secondary data-type="draft" class="save-form mr-3" data-type="draft">@lang('app.saveDraft')
                    </x-forms.button-secondary>
                    <x-forms.button-cancel :link="route('purchase-inventory.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>

        </x-form>
    </div>

</div>

<script>
    $(document).ready(function() {

        const dp1 = datepicker('#date', {
            position: 'bl',
            ...datepickerConfig
        });

        $("#reason").selectpicker();

        $('.toggle-product-category').click(function() {
            $('.product-category-filter').toggleClass('d-none');
        });

        $('#product_category_id').change(function(e) {
            let categoryId = $(this).val();
            let url = "{{ route('get_product_sub_categories', ':id') }}";

            url = (categoryId) ? url.replace(':id', categoryId) : url.replace(':id', null);

            window.apiHttp.get(url).then(function(response) {
                if (response.status == 'success') {
                    var options = [];
                    var rData;
                    rData = response.data;
                    $.each(rData, function(index, value) {
                        var selectData;
                        selectData = '<option value="' + value.id + '">' + value
                            .category_name + '</option>';
                        options.push(selectData);
                    });

                    $('#sub_category_id').html('<option value="">--</option>' +
                        options);
                    $('#sub_category_id').selectpicker('refresh');
                }
            }).catch(function(err) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        text: err.message,
                        toast: true,
                        position: 'top-end',
                        timer: 4000,
                        showConfirmButton: false
                    });
                }
            });
        });

        $('#warehouse_id').change(function() {
            // Warehouse change invalidates loaded rows because available quantity is warehouse-specific.
            $("#sortable .item-row").remove();
        });

        $('.save-form').click(function() {
            let formType = $(this).data('type');
            let data = $('#save-inventory-form').serialize();
            let url = "{{ route('purchase-inventory.store') }}" + "?formType=" + formType;
            var $saveBtns = $('.save-form');

            $saveBtns.prop('disabled', true);
            $.easyBlockUI('#save-inventory-form');
            window.apiHttp.postUrlEncoded(url, data).then(function(response) {
                if (response.status === 'success') {
                    window.location.href = "{{ route('purchase-inventory.index') }}";
                }

                if (typeof showTable !== 'undefined' && typeof showTable === 'function') {
                    showTable();
                }

            }).catch(function(err) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        text: err.message,
                        toast: true,
                        position: 'top-end',
                        timer: 4000,
                        showConfirmButton: false
                    });
                }
            }).finally(function() {
                $saveBtns.prop('disabled', false);
                $.easyUnblockUI('#save-inventory-form');
            });
        });

        const resetAddProductButton = () => {
            $("#add-products").val('').selectpicker("refresh");
        };

        $('#add-products').on('changed.bs.select', function(e, clickedIndex, isSelected, previousValue) {
            e.stopImmediatePropagation()
            var id = $(this).val();

            if (previousValue != id && id != '') {
                addProduct(id);
                resetAddProductButton();
            }
        });

        function addProduct(id) {

            let adjustmentVal = $('input[name="type"]:checked').val();
            let warehouseId = $('#warehouse_id').val();

            $.easyBlockUI('#save-inventory-form');
            window.apiHttp.get("{{ route('purchase_inventory.adjust_inventory') }}", {
                params: {
                    id: id,
                    val: adjustmentVal,
                    warehouse_id: warehouseId
                }
            }).then(function(response) {

                if ($('input[name="item_name[]"]').val() == '') {
                    $("#sortable .item-row").remove();
                }

                $(response.view).hide().appendTo("#sortable").fadeIn(500);
                $('.selectpicker').selectpicker('refresh');
                calculateTotal();
                $('.dropify').dropify();
                $('#alertMessage').hide().fadeOut(500);
                var noOfRows = $(document).find('#sortable .item-row').length;
                var i = $(document).find('.item_name').length - 1;
                var itemRow = $(document).find('#sortable .item-row:nth-child(' + noOfRows +
                    ') select.type');
                itemRow.attr('id', 'multiselect' + i);
                itemRow.attr('name', 'taxes[' + i + '][]');
                $(document).find('#multiselect' + i).selectpicker();

                $(document).find('#dropify' + i).dropify({
                    messages: dropifyMessages
                });
            }).catch(function(err) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        text: err.message,
                        toast: true,
                        position: 'top-end',
                        timer: 4000,
                        showConfirmButton: false
                    });
                }
            }).finally(function() {
                $.easyUnblockUI('#save-inventory-form');
            });
        }

        $('#save-inventory-form').on('click', '.remove-item', function() {
            $(this).closest('.item-row').fadeOut(300, function() {
                $(this).remove();
                $('select.customSequence').each(function(index) {
                    $(this).attr('name', 'taxes[' + index + '][]');
                    $(this).attr('id', 'multiselect' + index + '');
                });
            });
        });

        $('input[type=radio][name=type]').change(function() {
            $("#sortable .item-row").remove();
        });

        $('#addReason').click(function() {
            const url = "{{ route('adjustment-reasons.create') }}";
            $(MODAL_DEFAULT + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_DEFAULT, url);
        });

        init(RIGHT_MODAL);

    });
</script>
