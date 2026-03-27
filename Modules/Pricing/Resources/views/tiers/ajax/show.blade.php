<div class="row">
    <div class="col-sm-12">
        <div class="card bg-white border-0 b-shadow-4">
            <div class="card-header bg-white border-bottom-grey text-capitalize justify-content-between p-20">
                <div class="row">
                    <div class="col-lg-10 col-10">
                        <h3 class="heading-h1 mb-3">@lang('pricing::app.pricingTierDetails')</h3>
                    </div>
                    <div class="col-lg-2 col-2 text-right d-none">
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                <a class="dropdown-item openRightModal" href="{{ route('pricing.tiers.edit', $pricingTier->id) }}">@lang('app.edit')</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <p class="mb-0 text-lightest f-14 w-30 text-capitalize">@lang('app.name')</p>
                        <p class="mb-0 font-weight-bold f-14 text-dark-grey text-capitalize">
                            {{ $pricingTier->name }}
                        </p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-0 text-lightest f-14 w-30 text-capitalize">@lang('app.description')</p>
                        <p class="mb-0 font-weight-bold f-14 text-dark-grey text-capitalize">
                            {{ $pricingTier->description ?? '--' }}
                        </p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-0 text-lightest f-14 w-30 text-capitalize">@lang('app.status')</p>
                        <p class="mb-0 font-weight-bold f-14 text-dark-grey text-capitalize">
                            @if ($pricingTier->is_active)
                                <i class="fa fa-circle mr-1 text-light-green f-10"></i> @lang('app.active')
                            @else
                                <i class="fa fa-circle mr-1 text-red f-10"></i> @lang('app.inactive')
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Rules Section -->
        <div class="card bg-white border-0 b-shadow-4 mt-4">
            <div class="card-header bg-white border-bottom-grey text-capitalize justify-content-between p-20">
                <div class="row">
                    <div class="col-md-6">
                        <h3 class="heading-h1 mb-0">@lang('pricing::app.productRules')</h3>
                    </div>
                    <div class="col-md-6 text-right">
                        <x-forms.button-primary id="add-rule-btn" icon="plus">
                            @lang('pricing::app.addProductRule')
                        </x-forms.button-primary>
                    </div>
                </div>
            </div>

            <!-- Add Rule Form (Hidden by default) -->
            <div class="card-body bg-grey" id="add-rule-form-container" style="display: none;">
                <x-form id="add-item-form">
                    <div class="row">
                        <div class="col-md-4">
                            <x-forms.select fieldId="product_id" :fieldLabel="__('app.product')" fieldName="product_id" search="true">
                                <option value="">-- @lang('app.select') @lang('app.product') --</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </x-forms.select>
                        </div>
                        <div class="col-md-3">
                            <x-forms.select fieldId="discount_type" :fieldLabel="__('pricing::app.discountType')" fieldName="discount_type">
                                <option value="percentage">@lang('pricing::app.percentage') (%)</option>
                                <option value="fixed">@lang('pricing::app.fixedAmount')</option>
                                <option value="specific_price">@lang('pricing::app.specificPrice')</option>
                            </x-forms.select>
                        </div>
                        <div class="col-md-3">
                            <x-forms.number fieldId="discount_value" :fieldLabel="__('pricing::app.value')" fieldName="discount_value" fieldPlaceholder="0" />
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="d-block">&nbsp;</label>
                                <x-forms.button-primary id="save-item-btn" icon="check" type="button">
                                    @lang('app.add')
                                </x-forms.button-primary>
                            </div>
                        </div>
                    </div>
                </x-form>
            </div>

            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <div></div>
                    <div class="d-flex align-items-center">
                        <div class="mr-2">
                            <select class="form-control select-picker" id="items-quick-action-type" disabled>
                                <option value="">@lang('app.selectAction')</option>
                                <option value="delete">@lang('app.delete')</option>
                            </select>
                        </div>
                        <x-forms.button-primary class="mr-3" id="items-quick-action-apply" icon="check" disabled>
                            @lang('app.apply')
                        </x-forms.button-primary>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="pricing-tier-items-table">
                        <thead>
                            <tr>
                                <th style="width:40px"><input type="checkbox" id="select-all-items"></th>
                                <th>@lang('app.product')</th>
                                <th>@lang('pricing::app.discountType')</th>
                                <th>@lang('pricing::app.value')</th>
                                <th>@lang('purchase::modules.product.stockOnHand')</th>
                                <th>@lang('modules.unitType.unitType')</th>
                                <th>Client can purchase</th>
                                <th>@lang('app.status')</th>
                                <th class="text-right">@lang('app.action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pricingTier->items as $item)
                                <tr id="item-row-{{ $item->id }}">
                                    <td><input type="checkbox" class="select-items-row" value="{{ $item->id }}"></td>
                                    <td>{{ $item->product->name ?? '--' }}</td>
                                    <td>{{ ucfirst($item->discount_type) }}</td>
                                    <td>{{ $item->discount_value }}</td>
                                    <td>--</td>
                                    <td>{{ optional($item->product->unit)->unit_type ?? 'Pcs' }}</td>
                                    <td>
                                        @if (optional($item->product)->allow_purchase)
                                            <i class="fa fa-circle mr-1 text-light-green f-10"></i> Allowed
                                        @else
                                            <i class="fa fa-circle mr-1 text-red f-10"></i> Not allowed
                                        @endif
                                    </td>
                                    <td>
                                        @if (optional($item->product)->status === 'active')
                                            <i class="fa fa-circle mr-1 text-light-green f-10"></i> @lang('app.active')
                                        @else
                                            <i class="fa fa-circle mr-1 text-red f-10"></i> @lang('app.inactive')
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="javascript:;" class="btn btn-sm btn-danger delete-item" data-id="{{ $item->id }}">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="empty-space" style="height: 200px;">
                                            <div class="empty-space-inner">
                                                <div class="icon" style="font-size:30px"><i class="fa fa-box-open"></i>
                                                </div>
                                                <div class="title m-b-15">@lang('messages.noRecordFound')</div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // $.getScript("{{ asset('vendor/datatables/jquery.dataTables.min.js') }}");
        // $.getScript("{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}");
        // Toggle Add Form
        $('#add-rule-btn').click(function() {
            $('#add-rule-form-container').slideToggle();
        });

        // Initialize Select2
        // $("#product_id").select2();

        // Save Item
        $('body').on('click', '#save-item-btn', function(e) {
            e.preventDefault();
            $.easyBlockUI('#add-item-form');
            window.apiHttp.postUrlEncoded("{{ route('pricing.tiers.items.store', $pricingTier->id) }}", $('#add-item-form').serialize())
                .then(function(response) {
                    if (response.status == 'success') {
                        window.location.reload();
                    }
                })
                .catch(function(err) {
                    $.handleApiFormError(err);
                })
                .finally(function() {
                    $.easyUnblockUI('#add-item-form');
                });
        });

        // Delete Item
        $('body').on('click', '.delete-item', function() {
            var id = $(this).data('id');
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
                    var url = "{{ route('pricing.tiers.items.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    window.apiHttp.delete(url, "{{ csrf_token() }}")
                        .then(function(response) {
                            if (response.status == "success") {
                                $('#item-row-' + id).remove();
                            }
                        })
                        .catch(function(err) {
                            $.handleApiFormError(err);
                        });
                }
            });
        });

        // Items Bulk Select
        $('#select-all-items').change(function() {
            var isChecked = $(this).prop('checked');
            $('.select-items-row').prop('checked', isChecked);
            toggleItemsQuickAction();
        });

        $('body').on('change', '.select-items-row', function() {
            toggleItemsQuickAction();
        });

        function toggleItemsQuickAction() {
            var checkedCount = $('.select-items-row:checked').length;
            if (checkedCount > 0) {
                $('#items-quick-action-type').prop('disabled', false);
                $('#items-quick-action-apply').prop('disabled', false);
                $('#items-quick-action-type').selectpicker('refresh');
            } else {
                $('#items-quick-action-type').prop('disabled', true);
                $('#items-quick-action-type').val('');
                $('#items-quick-action-apply').prop('disabled', true);
                $('#items-quick-action-type').selectpicker('refresh');
            }
        }

        // Items Bulk Delete Apply
        $('#items-quick-action-apply').click(function() {
            var actionValue = $('#items-quick-action-type').val();
            if (actionValue == 'delete') {
                var rowIds = $(".select-items-row:checked").map(function() {
                    return $(this).val();
                }).get();
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
                        window.apiHttp.postUrlEncoded("{{ route('pricing.tiers.items.apply_quick_action') }}", {
                            '_token': "{{ csrf_token() }}",
                            action_type: 'delete',
                            row_ids: rowIds.join(',')
                        })
                            .then(function(response) {
                                if (response.status == 'success') {
                                    window.location.reload();
                                }
                            })
                            .catch(function(err) {
                                $.handleApiFormError(err);
                            });
                    }
                });
            }
        });

        // Enable sorting on items table
        $.getScript("{{ asset('vendor/datatables/jquery.dataTables.min.js') }}", function() {
            $.getScript("{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}", function() {
                $('#pricing-tier-items-table').DataTable({
                    ordering: true,
                    searching: false,
                    paging: false,
                    info: false,
                    columnDefs: [{
                        orderable: false,
                        targets: [0, 8]
                    }],
                    order: [
                        [1, 'asc']
                    ]
                });
            });
        });

        init();
    });
</script>
