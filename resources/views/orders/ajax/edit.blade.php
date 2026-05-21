@php
    $addProductPermission = user()->permission('add_product');
@endphp
<style>
    .customSequence .btn {
        border: none;
    }
</style>

<!-- for sortable content -->
<link rel="stylesheet" href="{{ asset('vendor/css/jquery-ui.css') }}">


<!-- CREATE ORDER START -->
<div class="bg-white rounded b-shadow-4 create-inv">
    <!-- HEADING START -->
    <div class="px-lg-4 px-md-4 px-3 py-3">
        <h4 class="mb-0 f-21 font-weight-normal ">@lang('app.orderDetails')</h4>
    </div>
    <!-- HEADING END -->
    <hr class="m-0 border-top-grey">
    <!-- FORM START -->
    <x-form class="c-inv-form" id="saveOrderForm">
        @method('PUT')
        <!-- ORDER NUMBER, DATE, DUE DATE, FREQUENCY START -->
        <div class="row px-lg-4 px-md-4 px-3 py-3">
            <!-- ORDER NUMBER START -->
            <div class="col-md-4">
                <div class="form-group mb-lg-0 mb-md-0 mb-4">
                    <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('modules.orders.orderNumber')</label>
                    <div class="input-group">
                        <input type="text" name="order_id" id="order_id" class="form-control height-35 f-15 readonly-background" readonly value="{{ $order->order_number }}">
                    </div>
                </div>
            </div>
            <!-- ORDER NUMBER END -->
            @if (!in_array('client', user_roles()))
                <!-- Order Status -->

                <div class="col-md-4">
                    <div class="form-group c-inv-select mb-4">
                        <x-forms.label fieldId="company_address_id" :fieldLabel="__('modules.invoices.generatedBy')">
                        </x-forms.label>
                        <div class="select-others height-35 rounded">
                            <select class="form-control select-picker" data-live-search="true" data-size="8" name="company_address_id" id="company_address_id">
                                @foreach ($companyAddresses as $item)
                                    <option {{ $item->id == $order->company_address_id ? 'selected' : '' }} value="{{ $item->id }}">
                                        {{ $item->location }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>


                <div class="col-md-4">
                    <x-forms.label fieldId="status" :fieldLabel="__('app.status')" :fieldRequired="true" class="mt-0"></x-forms.label>

                    <select class="form-control select-picker" name="status" id="status">
                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }} data-content="<i class='fa fa-circle mr-2 text-yellow'></i> @lang('app.pending') ">@lang('app.pending')</option>

                        <option value="on-hold" {{ $order->status == 'on-hold' ? 'selected' : '' }} data-content="<i class='fa fa-circle mr-2 text-info'></i> @lang('app.on-hold') ">@lang('app.on-hold')</option>

                        <option value="failed" {{ $order->status == 'failed' ? 'selected' : '' }} data-content="<i class='fa fa-circle mr-2 text-muted'></i> @lang('app.failed') ">@lang('app.failed')</option>

                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }} data-content="<i class='fa fa-circle mr-2 text-blue'></i> @lang('app.processing') ">@lang('app.processing')</option>

                        <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }} data-content="<i class='fa fa-circle mr-2 text-dark-green'></i> @lang('app.completed') ">@lang('app.completed')</option>

                        <option value="canceled" {{ $order->status == 'canceled' ? 'selected' : '' }} data-content="<i class='fa fa-circle mr-2 text-red'></i> @lang('app.canceled') ">@lang('app.canceled')</option>

                    </select>
                </div>
            @endif
            <input type="hidden" id="calculate_tax" value="after_discount">
        </div>

        <!-- ORDER NUMBER, DATE, DUE DATE, FREQUENCY END -->

        <hr class="m-0 border-top-grey">

        <div class="row px-lg-4 px-md-4 px-3 py-3">
            <div class="col-md-3 d-none product-category-filter">
                <div class="form-group c-inv-select mb-4">
                    <x-forms.input-group>
                        <select class="form-control select-picker" name="category_id" id="product_category_id" data-live-search="true">
                            <option value="">{{ __('app.select') . ' ' . __('app.product') . ' ' . __('app.category') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </x-forms.input-group>
                </div>
            </div>

            @if (in_array('products', user_modules()) || in_array('purchase', user_modules()))
                <div class="col-md-3">
                    <div class="form-group c-inv-select mb-4">
                        <x-forms.input-group>
                            <select class="form-control select-picker" data-live-search="true" data-size="8" id="add-products" title="{{ __('app.menu.selectProduct') }}">
                                @foreach ($products as $item)
                                    <option data-content="{{ $item->name }} @if ($item->sku) ({{ $item->sku }}) @endif" value="{{ $item->id }}">
                                        {{ $item->name }}</option>
                                @endforeach
                            </select>
                            <x-slot name="preappend">
                                <a href="javascript:;" class="btn btn-outline-secondary border-grey toggle-product-category" data-toggle="tooltip" data-original-title="{{ __('modules.productCategory.filterByCategory') }}"><i class="fa fa-filter"></i></a>
                            </x-slot>
                            @if ($addProductPermission == 'all' || $addProductPermission == 'added')
                                <x-slot name="append">
                                    <a href="{{ route('products.create') }}" data-redirect-url="no" class="btn btn-outline-secondary border-grey openRightModal" data-toggle="tooltip" data-original-title="{{ __('app.add') . ' ' . __('modules.dashboard.newproduct') }}">@lang('app.add')</a>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>
                </div>
            @endif
        </div>

        <div id="sortable">
            @foreach ($order->items->sortBy('field_order') as $key => $item)
                <!-- DESKTOP DESCRIPTION TABLE START -->
                <div class="d-flex px-4 py-3 c-inv-desc item-row">
                    <div class="d-flex align-items-center">
                        <span class="ui-icon ui-icon-arrowthick-2-n-s mr-2"></span>
                        <input type="hidden" name="sort_order[]" value="{{ $item->id }}">
                    </div>

                    <div class="c-inv-desc-table w-100 d-lg-flex d-md-flex d-block">
                        <table width="100%">
                            <tbody>
                                <tr class="text-dark-grey font-weight-bold f-14">
                                    <td width="{{ $invoiceSetting->hsn_sac_code_show ? '40%' : '50%' }}" class="border-0 inv-desc-mbl btlr">@lang('app.description')</td>
                                    @if ($invoiceSetting->hsn_sac_code_show)
                                        <td width="10%" class="border-0" align="right">@lang('app.hsnSac')</td>
                                    @endif
                                    <td width="10%" class="border-0" align="right" id="type">
                                        @lang('modules.invoices.qty')
                                    </td>
                                    <td width="10%" class="border-0" align="right" id="type">
                                        @lang('app.sku')
                                    </td>
                                    <td width="10%" class="border-0" align="right">
                                        @lang('modules.invoices.unitPrice')</td>
                                    <td width="13%" class="border-0" align="right">@lang('modules.invoices.tax')
                                    </td>
                                    <td width="17%" class="border-0 bblr-mbl" align="right">
                                        @lang('modules.invoices.amount')</td>
                                </tr>
                                <tr>
                                    <td class="border-bottom-0 btrr-mbl btlr">
                                        <input type="text" class="form-control f-14 border-0 w-100 item_name" readonly name="item_name[]" placeholder="@lang('modules.expenses.itemName')" value="{{ $item->item_name }}">
                                    </td>
                                    <td class="border-bottom-0 d-block d-lg-none d-md-none">
                                        <textarea class="f-14 border-0 w-100 mobile-description" placeholder="@lang('placeholders.invoices.description')" readonly name="item_summary[]">{{ $item->item_summary }}</textarea>
                                    </td>
                                    @if ($invoiceSetting->hsn_sac_code_show)
                                        <td class="border-bottom-0">
                                            <input type="text" class="f-14 border-0 w-100 text-right hsn_sac_code" value="{{ $item->hsn_sac_code }}" name="hsn_sac_code[]">
                                        </td>
                                    @endif
                                    <td class="border-bottom-0">
                                        <input type="number" min="1" class="form-control f-14 border-0 w-100 text-right quantity mt-3" value="{{ $item->quantity }}" name="quantity[]">
                                        @if (!is_null($item->product_id) && $item->product_id != 0)
                                            <input type="hidden" name="product_id[]" value="{{ $item->product_id }}">
                                            @include('orders.partials.item-unit-select', [
                                                'sellableUnits' => $productSellableUnitsMap[$item->product_id] ?? [],
                                                'selectedUnitId' => $item->unit_id,
                                                'productId' => $item->product_id,
                                                'fallbackUnitLabel' => $item->unit?->unit_type,
                                            ])
                                        @else
                                            <select class="text-dark-grey float-right border-0 f-12" name="unit_id[]">
                                                @foreach ($units as $unit)
                                                    <option @selected($item->unit_id == $unit->id) value="{{ $unit->id }}">{{ $unit->unit_type }}</option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="product_id[]" value="">
                                        @endif
                                    </td>
                                    <td class="border-bottom-0">
                                        <input type="text" min="1" class="f-14 border-0 w-100 text-right form-control" placeholder="--" value="{{ $item->sku }}" name="sku[]" readonly>
                                    </td>
                                    <td class="border-bottom-0">
                                        <input type="number" min="1" class="f-14 border-0 w-100 text-right cost_per_item bg-additional-grey" placeholder="0.00" value="{{ $item->unit_price }}" name="cost_per_item[]" {{ $user->isAdmin($user->id) ? '' : 'readonly' }}>
                                    </td>
                                    <td class="border-bottom-0">
                                        <input class="form-control height-35 f-14 border-0 w-100 text-right bg-additional-grey " value="{{ $item->tax_list ?: '--' }}" readonly>
                                        <div class="select-others  d-none height-35 rounded border-0">
                                            <select id="multiselect{{ $key }}" name="taxes[{{ $key }}][]" multiple="multiple" class="select-picker type customSequence border-0" data-size="3">
                                                @foreach ($taxes as $tax)
                                                    <option data-rate="{{ $tax->rate_percent }}" data-tax-text="{{ $tax->tax_name . ':' . $tax->rate_percent }}%" @if (isset($item->taxes) && array_search($tax->id, json_decode($item->taxes)) !== false) selected @endif value="{{ $tax->id }}">{{ $tax->tax_name }}:
                                                        {{ $tax->rate_percent }}%</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>
                                    <td rowspan="2" align="right" valign="top" class="bg-amt-grey btrr-bbrr">
                                        <span class="amount-html">{{ number_format((float) $item->amount, 2, '.', '') }}</span>
                                        <input type="hidden" class="amount" name="amount[]" value="{{ $item->amount }}">
                                    </td>
                                </tr>
                                <tr class="d-none d-md-block d-lg-table-row">
                                    <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '4' : '3' }}" class="dash-border-top bblr">
                                        <textarea class="f-14 border-0 w-100 desktop-description" name="item_summary[]" placeholder="@lang('placeholders.invoices.description')" readonly>{{ $item->item_summary }}</textarea>
                                    </td>
                                    <td class="border-left-0">
                                        <input type="file" class="dropify" name="invoice_item_image[]" data-allowed-file-extensions="png jpg jpeg bmp" data-messages-default="test" data-height="70" data-id="{{ $item->id }}" id="{{ $item->id }}" data-default-file="{{ $item->orderItemImage ? $item->orderItemImage->file_url : null }}" disabled />
                                        <input type="hidden" name="invoice_item_image_url[]" value="{{ $item->orderItemImage ? $item->orderItemImage->file : '' }}">
                                        <input type="hidden" name="item_ids[]" value="{{ $item->id }}">
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <a href="javascript:;" class="d-flex align-items-center justify-content-center ml-3 remove-item"><i class="fa fa-times-circle f-20 text-lightest"></i></a>
                    </div>
                </div>
                <!-- DESKTOP DESCRIPTION TABLE END -->
            @endforeach
        </div>

        <hr class="m-0 border-top-grey">

        <!-- TOTAL, DISCOUNT START -->
        <div class="d-flex px-lg-4 px-md-4 px-3 pb-3 c-inv-total">
            <table width="100%" class="text-right f-14 ">
                <tbody>
                    <tr>
                        <td width="50%" class="border-0 d-lg-table d-md-table d-none"></td>
                        <td width="50%" class="p-0 border-0 c-inv-total-right">
                            <table width="100%">
                                <tbody>
                                    <tr>
                                        <td colspan="2" class="border-top-0 text-dark-grey">
                                            @lang('modules.invoices.subTotal')</td>
                                        <td width="30%" class="border-top-0 sub-total">
                                            {{ number_format((float) $order->sub_total, 2, '.', '') }}</td>
                                        <input type="hidden" class="sub-total-field" name="sub_total" value="{{ $order->sub_total }}">
                                    </tr>
                                    {{-- {{in_array('client', user_roles()) ? 'd-none' : ''}} --}}
                                    <tr class="">
                                        <td width="30%" class="text-dark-grey">@lang('modules.invoices.discount')
                                        </td>
                                        <td width="30%" style="padding: 5px;">
                                            <table width="100%">
                                                <tbody>
                                                    <tr>
                                                        <td width="50%" class="c-inv-sub-padding">
                                                            <input type="number" min="0" name="discount_value" {{ in_array('client', user_roles()) ? 'readonly' : '' }} class="form-control f-14 border-0 w-100 text-right discount_value" placeholder="0" value="{{ $order->discount }}">
                                                        </td>
                                                        <td width="50%" align="left" class="c-inv-sub-padding">
                                                            @if (in_array('client', user_roles()))
                                                                @if ($order->discount_type == 'percent')
                                                                    %
                                                                @else
                                                                    @lang('modules.invoices.amount')
                                                                @endif
                                                            @endif
                                                            <div class="select-others select-tax height-35 rounded border-0 {{ in_array('client', user_roles()) ? 'd-none' : '' }}">

                                                                <select class="form-control select-picker" id="discount_type" name="discount_type">
                                                                    <option @selected($order->discount_type == 'percent') value="percent">%
                                                                    </option>
                                                                    <option @selected($order->discount_type == 'fixed') value="fixed">
                                                                        @lang('modules.invoices.amount')</option>
                                                                </select>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                        <td><span id="discount_amount">{{ number_format((float) $order->discount, 2, '.', '') }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>@lang('modules.invoices.tax')</td>
                                        <td colspan="2" class="p-0">
                                            <table width="100%" id="invoice-taxes">
                                                <tr>
                                                    <td colspan="2"><span class="tax-percent">0.00</span></td>
                                                </tr>
                                            </table>
                                        </td>

                                    </tr>
                                    <tr class="bg-amt-grey f-16 f-w-500">
                                        <td colspan="2">@lang('modules.invoices.total')</td>
                                        <td><span class="total">{{ number_format((float) $order->total, 2, '.', '') }}</span>
                                        </td>
                                        <input type="hidden" class="total-field" name="total" value="{{ round($order->total, 2) }}">
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- TOTAL, DISCOUNT END -->

        <!-- NOTE AND TERMS AND CONDITIONS START -->
        <div class="d-flex flex-wrap px-lg-4 px-md-4 px-3 py-3">
            <div class="col-md-6 col-sm-12 c-inv-note-terms p-0 mb-lg-0 mb-md-0 mb-3">
                <label class="f-14 text-dark-grey mb-12  w-100" for="usr">@lang('app.clientNote')</label>
                <textarea class="form-control" name="note" id="note" rows="4" placeholder="@lang('placeholders.invoices.note')">{{ $order->note }}</textarea>
            </div>
        </div>

        <x-forms.custom-field :fields="$fields" :model="$order"></x-forms.custom-field>
        <!-- NOTE AND TERMS AND CONDITIONS END -->

        <!-- CANCEL SAVE SEND START -->
        <x-form-actions class="c-inv-btns">

            <div class="d-flex">
                <x-forms.button-primary class="save-form mr-3" icon="check">@lang('app.save')
                </x-forms.button-primary>
            </div>

            <x-forms.button-cancel :link="route('invoices.index')" class="border-0">@lang('app.cancel')
            </x-forms.button-cancel>
        </x-form-actions>
        <!-- CANCEL SAVE SEND END -->

    </x-form>
    <!-- FORM END -->
</div>
<!-- CREATE ORDER END -->

<!-- for sortable content -->
<script src="{{ asset('vendor/jquery/jquery-ui.min.js') }}"></script>
@include('sections.jquery_ui_restore_bootstrap_tooltip')

<script>
    $(function() {
        $("#sortable").sortable();
    });

    $(document).ready(function() {
        function escapeHtml(value) {
            return $('<div>').text(value == null ? '' : String(value)).html();
        }

        function debounce(fn, wait) {
            var timer = null;
            return function() {
                var ctx = this;
                var args = arguments;
                clearTimeout(timer);
                timer = setTimeout(function() {
                    fn.apply(ctx, args);
                }, wait);
            };
        }

        function initRemoteBootstrapSelect(selectSelector, endpointUrl, optionBuilder) {
            var $select = $(selectSelector);
            if (!$select.length) {
                return;
            }

            var state = {
                term: '',
                page: 1,
                loading: false,
                hasMore: true,
                requestId: 0
            };

            function selectedValues() {
                var val = $select.val();
                if (!val) {
                    return [];
                }
                return Array.isArray(val) ? val : [val];
            }

            function replaceOptions(items) {
                var selected = selectedValues();
                var html = '<option value="">{{ __('app.menu.selectProduct') }}</option>';

                $.each(items, function(_, item) {
                    html += optionBuilder(item, selected);
                });

                $select.html(html);
                $select.selectpicker('refresh');
            }

            function appendOptions(items) {
                var selected = selectedValues();
                $.each(items, function(_, item) {
                    if ($select.find('option[value="' + item.id + '"]').length) {
                        return;
                    }
                    $select.append(optionBuilder(item, selected));
                });
                $select.selectpicker('refresh');
            }

            function load(term, page, appendMode) {
                if (state.loading) {
                    return;
                }

                state.loading = true;
                var currentRequestId = ++state.requestId;

                window.apiHttp.get(endpointUrl, {
                    params: {
                        q: term,
                        page: page,
                        per_page: 50
                    }
                }).then(function(response) {
                    if (currentRequestId !== state.requestId) {
                        return;
                    }

                    var items = response.items || [];
                    state.hasMore = !!(response.pagination && response.pagination.has_more);
                    state.page = page;

                    if (appendMode) {
                        appendOptions(items);
                    } else {
                        replaceOptions(items);
                    }
                }).catch(function(err) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            text: err.message || "@lang('messages.somethingWentWrong')",
                            toast: true,
                            position: 'top-end',
                            timer: 4000,
                            showConfirmButton: false
                        });
                    }
                }).finally(function() {
                    state.loading = false;
                });
            }

            $select.on('shown.bs.select', function() {
                var $picker = $select.parent();
                var $searchInput = $picker.find('.bs-searchbox input');
                var $inner = $picker.find('.inner');

                $searchInput.off('.remoteSelect').on('input.remoteSelect', debounce(function() {
                    state.term = ($(this).val() || '').trim();
                    state.page = 1;
                    state.hasMore = true;
                    load(state.term, 1, false);
                }, 300));

                $inner.off('.remoteSelect').on('scroll.remoteSelect', function() {
                    var nearBottom = this.scrollTop + this.clientHeight >= this.scrollHeight - 24;
                    if (!nearBottom || !state.hasMore || state.loading) {
                        return;
                    }
                    load(state.term, state.page + 1, true);
                });
            });
        }

        initRemoteBootstrapSelect('#add-products', "{{ route('orders.search_products') }}", function(item, selected) {
            var label = escapeHtml(item.name || '');
            var sku = item.sku ? ' (' + escapeHtml(item.sku) + ')' : '';
            var isSelected = selected.indexOf(String(item.id)) !== -1 || selected.indexOf(item.id) !== -1;
            return '<option value="' + item.id + '"' + (isSelected ? ' selected' : '') + ' data-content="' + label + sku + '">' + label + '</option>';
        });

        $('.toggle-product-category').click(function() {
            $('.product-category-filter').toggleClass('d-none');
            var url = "{{ route('invoices.product_category', ':id') }}";
            url = url.replace(':id', null);
            changeProductCategory(url);
            $('#product_category_id').val('').trigger('change');
            $('#product_category_id').selectpicker('refresh');
        });

        $('#product_category_id').on('change', function() {
            var categoryId = $(this).val();
            var url = "{{ route('invoices.product_category', ':id') }}";
            url = (categoryId) ? url.replace(':id', categoryId) : url.replace(':id', null);
            changeProductCategory(url);
        });

        function changeProductCategory(url) {
            $.easyBlockUI('#saveOrderForm');
            window.apiHttp.get(url).then(function(response) {
                if (response.status == 'success') {
                    var options = [];
                    var rData = [];
                    rData = response.data;
                    $.each(rData, function(index, value) {
                        var selectData = '';
                        selectData = '<option value="' + value.id + '">' + value.name +
                            '</option>';
                        options.push(selectData);
                    });
                    $('#add-products').html(
                        '<option value="" class="form-control" >{{ __('app.menu.selectProduct') }}</option>' +
                        options);
                    $('#add-products').selectpicker('refresh');
                }
            }).catch(function(err) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        text: getReadableApiError(err),
                        toast: true,
                        position: 'top-end',
                        timer: 7000,
                        showConfirmButton: false
                    });
                }
            }).finally(function() {
                $.easyUnblockUI('#saveOrderForm');
            });
        }

        function getReadableApiError(err) {
            if (err && err.errors && typeof err.errors === 'object') {
                var messages = [];
                Object.keys(err.errors).forEach(function(key) {
                    var val = err.errors[key];
                    if (Array.isArray(val)) {
                        val.forEach(function(item) {
                            if (item) {
                                messages.push(item);
                            }
                        });
                    } else if (val) {
                        messages.push(val);
                    }
                });

                if (messages.length) {
                    return messages.slice(0, 4).join('\n');
                }
            }

            return (err && err.message) ? err.message : "@lang('messages.somethingWentWrong')";
        }


        $('.custom-date-picker').each(function(ind, el) {
            datepicker(el, {
                position: 'bl',
                ...datepickerConfig
            });
        });

        $('#client_id').change(function() {
            var id = $(this).val();
            var token = "{{ csrf_token() }}";

            var urlProjects = "{{ route('clients.project_list', ':id') }}".replace(':id', id);
            var urlDetails = "{{ route('clients.ajax_details', ':id') }}".replace(':id', id);
            var postBody = '_token=' + encodeURIComponent(token);

            $.easyBlockUI('#saveOrderForm');
            Promise.all([
                window.apiHttp.postUrlEncoded(urlProjects, postBody),
                window.apiHttp.postUrlEncoded(urlDetails, postBody)
            ]).then(function(results) {
                var responseProj = results[0];
                var response = results[1];
                if (responseProj.status == 'success') {
                    $('#project_id').html(responseProj.data);
                    $('#project_id').selectpicker('refresh');
                }
                if (response.status == 'success') {
                    $('#client_billing_address').html(nl2br(response.data.clientDetails
                        .address));
                    $('#add-shipping-field').addClass('d-none');
                    $('#client_shipping_address').removeClass('d-none');

                    if (response.data.clientDetails.shipping_address === null) {
                        var addShippingLink =
                            `<a href="javascript:;" class="" id="show-shipping-field"><i class="f-12 mr-2 fa fa-plus"></i>
                                @lang('app.addShippingAddress')</a>`;
                        $('#client_shipping_address').html(addShippingLink);
                    } else {
                        $('#client_shipping_address').html(nl2br(response.data
                            .clientDetails
                            .shipping_address));
                    }
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
                $.easyUnblockUI('#saveOrderForm');
            });

        });

        $('body').on('click', '#show-shipping-field', function() {
            $('#add-shipping-field, #client_shipping_address').toggleClass('d-none');
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


        function ucWord(str) {
            str = str.toLowerCase().replace(/\b[a-z]/g, function(letter) {
                return letter.toUpperCase();
            });
            return str;
        }

        function addProduct(id) {

            var existingRow = $(`input[name="product_id[]"][value="${id}"]`).closest('.item-row');

            if (existingRow.length) {
                // Increase quantity
                let qtyInput = existingRow.find('input.quantity');
                let currentQty = parseFloat(qtyInput.val());
                qtyInput.val(currentQty + 1).trigger('change'); // Trigger change to recalculate amount

                let cost = existingRow.find('input.cost_per_item');
                let amountHtml = existingRow.find('span.amount-html');
                let amount = existingRow.find('input.amount');
                let newAmount = (qtyInput.val() * cost.val());
                amountHtml.html(newAmount).trigger('change');
                amount.val(newAmount).trigger('change');

                calculateTotal();

                return; // Exit the function
            }

            var currencyId = $('#currency_id').val();

            $.easyBlockUI('#saveOrderForm');
            window.apiHttp.get("{{ route('orders.add_item') }}", {
                params: {
                    id: id,
                    currencyId: currencyId
                }
            }).then(function(response) {
                if ($('input[name="item_name[]"]').val() == '') {
                    $("#sortable .item-row").remove();
                }
                $(response.view).hide().appendTo("#sortable").fadeIn(500);
                calculateTotal();

                var noOfRows = $(document).find('#sortable .item-row').length;
                var i = $(document).find('.item_name').length - 1;
                var itemRow = $(document).find('#sortable .item-row:nth-child(' + noOfRows +
                    ') select.type');
                itemRow.attr('id', 'multiselect' + i);
                itemRow.attr('name', 'taxes[' + i + '][]');
                $(document).find('#multiselect' + i).selectpicker();
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
                $.easyUnblockUI('#saveOrderForm');
            });
        }

        $('#saveOrderForm').on('click', '.remove-item', function() {
            $(this).closest('.item-row').fadeOut(300, function() {
                $(this).remove();
                $('select.customSequence').each(function(index) {
                    $(this).attr('name', 'taxes[' + index + '][]');
                    $(this).attr('id', 'multiselect' + index + '');
                });
                calculateTotal();
            });
        });

        $('.save-form').click(function() {

            if (KTUtil.isMobileDevice()) {
                $('.desktop-description').remove();
            } else {
                $('.mobile-description').remove();
            }

            calculateTotal();

            var discount = $('#discount_amount').html();
            var total = $('.sub-total-field').val();

            if (parseFloat(discount) > parseFloat(total)) {
                Swal.fire({
                    icon: 'error',
                    text: "{{ __('messages.discountExceed') }}",

                    customClass: {
                        confirmButton: 'btn btn-primary',
                    },
                    showClass: {
                        popup: 'swal2-noanimation',
                        backdrop: 'swal2-noanimation'
                    },
                    buttonsStyling: false
                });
                return false;
            }

            var $saveBtns = $('.save-form');
            var updateBody = $('#saveOrderForm').serialize();
            $saveBtns.prop('disabled', true);
            $.easyBlockUI('#saveOrderForm');
            window.apiHttp.postUrlEncoded("{{ route('orders.update', $order->id) }}", updateBody).then(function(response) {
                if (response.status === 'success') {
                    if (response.action === 'redirect' && response.url) {
                        window.location.href = response.url;
                    } else if (response.redirectUrl) {
                        window.location.href = response.redirectUrl;
                    }
                }
            }).catch(function(err) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        text: getReadableApiError(err),
                        toast: true,
                        position: 'top-end',
                        timer: 7000,
                        showConfirmButton: false
                    });
                }
            }).finally(function() {
                $saveBtns.prop('disabled', false);
                $.easyUnblockUI('#saveOrderForm');
            });
        });

        $('#saveOrderForm').on('click', '.remove-item', function() {
            $(this).closest('.item-row').fadeOut(300, function() {
                $(this).remove();
                $('select.customSequence').each(function(index) {
                    $(this).attr('name', 'taxes[' + index + '][]');
                    $(this).attr('id', 'multiselect' + index + '');
                });
                calculateTotal();
            });
        });

        $('#saveOrderForm').on('keyup', '.quantity,.cost_per_item,.item_name, .discount_value', function() {
            var quantity = $(this).closest('.item-row').find('.quantity').val();
            var perItemCost = $(this).closest('.item-row').find('.cost_per_item').val();
            var amount = (quantity * perItemCost);

            $(this).closest('.item-row').find('.amount').val(decimalupto2(amount));
            $(this).closest('.item-row').find('.amount-html').html(decimalupto2(amount));

            calculateTotal();
        });

        $('#saveOrderForm').on('change', '.type, #discount_type, #calculate_tax', function() {
            var quantity = $(this).closest('.item-row').find('.quantity').val();
            var perItemCost = $(this).closest('.item-row').find('.cost_per_item').val();
            var amount = (quantity * perItemCost);

            $(this).closest('.item-row').find('.amount').val(decimalupto2(amount));
            $(this).closest('.item-row').find('.amount-html').html(decimalupto2(amount));

            calculateTotal();
        });

        $('#saveOrderForm').on('input', '.quantity', function() {
            var quantity = $(this).closest('.item-row').find('.quantity').val();
            var perItemCost = $(this).closest('.item-row').find('.cost_per_item').val();
            var amount = (quantity * perItemCost);

            $(this).closest('.item-row').find('.amount').val(decimalupto2(amount));
            $(this).closest('.item-row').find('.amount-html').html(decimalupto2(amount));

            calculateTotal();
        });

        function applyOrderLineUnitPrice($select) {
            var price = $select.find(':selected').data('unit-price');
            var $row = $select.closest('.item-row');
            if (price === undefined || price === '') {
                return;
            }
            $row.find('.cost_per_item').val(price).trigger('change');
            var quantity = parseFloat($row.find('.quantity').val()) || 1;
            var amount = decimalupto2(quantity * parseFloat(price));
            $row.find('.amount').val(amount);
            $row.find('.amount-html').html(amount);
            calculateTotal();
        }

        $('#saveOrderForm .order-line-unit-select').selectpicker();
        $('#saveOrderForm').on('changed.bs.select change', '.order-line-unit-select', function() {
            applyOrderLineUnitPrice($(this));
        });

        calculateTotal();

        init(RIGHT_MODAL);

    });
</script>
