@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if (in_array('admin', user_roles()) || user()->permission('add_client_pricing') == 'all' || user()->permission('add_client_pricing') == 'added')
                    <x-forms.link-primary :link="route('pricing.company_pricing.create')" class="mr-3 openRightModal float-left" icon="plus">
                        @lang('pricing::app.addCompanyPricing')
                    </x-forms.link-primary>
                @endif
            </div>
            <x-datatable.actions>
                <div class="select-status mr-3 pl-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="change-status">@lang('modules.tasks.changeStatus')</option>
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
                <div class="select-status mr-3 d-none status-quick-action" id="change-status-action">
                    <select name="status" class="form-control select-picker">
                        <option value="active">@lang('app.active')</option>
                        <option value="inactive">@lang('app.inactive')</option>
                    </select>
                </div>
            </x-datatable.actions>
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            <table class="table table-hover border-0 w-100" id="company-pricing-table">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="select-all-table">
                        </th>
                        <th>@lang('app.company_name')</th>
                        <th>@lang('pricing::app.pricingTier')</th>
                        <th>@lang('pricing::app.globalDiscount')</th>
                        <th>@lang('app.status')</th>
                        <th class="text-right">@lang('app.action')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pricings as $row)
                        <tr id="row-{{ $row->id }}">
                            <td>
                                <input type="checkbox" class="select-table-row" id="datatable-row-{{ $row->id }}" name="datatable_ids[]" value="{{ $row->id }}">
                            </td>
                            <td data-order="{{ $row->client?->name ?? '' }}">
                                <div class="media align-items-center">
                                    <div class="media-body">
                                        <h5 class="mb-0 f-13 text-darkest-grey">
                                            {{ $row->client?->name ?? '--' }}
                                        </h5>
                                        <p class="mb-0 f-12 text-dark-grey">{{ $row->client?->email }}</p>
                                        @if($row->client?->clientDetails?->company_name)
                                            <p class="mb-0 f-11 text-light-grey">{{ $row->client->clientDetails->company_name }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td data-order="{{ $row->tier?->name ?? '' }}">{{ $row->tier?->name ?? '--' }}</td>
                            <td data-order="{{ $row->custom_discount_value ?? 0 }}">
                                @if ($row->custom_discount_type && $row->custom_discount_value)
                                    {{ $row->custom_discount_value }} 
                                    ({{ $row->custom_discount_type == 'percentage' ? '%' : 'Fixed' }})
                                @else
                                    --
                                @endif
                            </td>
                            <td>
                                <select class="form-control select-picker change-pricing-status" data-pricing-id="{{ $row->id }}">
                                    <option value="active" data-content="<i class='fa fa-circle text-light-green'></i> @lang('app.active')" {{ $row->is_active ? 'selected' : '' }}>
                                        @lang('app.active')
                                    </option>
                                    <option value="inactive" data-content="<i class='fa fa-circle text-red'></i> @lang('app.inactive')" {{ !$row->is_active ? 'selected' : '' }}>
                                        @lang('app.inactive')
                                    </option>
                                </select>
                            </td>
                            <td class="text-right">
                                @if (in_array('admin', user_roles()) || user()->permission('add_client_pricing') == 'all' || user()->permission('add_client_pricing') == 'added' || user()->permission('edit_client_pricing') != 'none')
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary border-0 dropdown-toggle" type="button" id="actionDropdown{{ $row->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fa fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="actionDropdown{{ $row->id }}">
                                            <a class="dropdown-item openRightModal" href="{{ route('pricing.company_pricing.edit', $row->id) }}">
                                                <i class="fa fa-edit mr-2"></i> @lang('app.edit')
                                            </a>
                                            <a href="javascript:;" class="dropdown-item delete-company-pricing" data-row-id="{{ $row->id }}">
                                                <i class="fa fa-trash mr-2"></i> @lang('app.delete')
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script>
        $(function() {
            $('#company-pricing-table').DataTable({
                dom: "<'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
                responsive: true,
                language: {
                    "url": "{{ asset('vendor/datatables/'.app()->getLocale().'.json') }}"
                },
                "fnDrawCallback": function( oSettings ) {
                    $("body").tooltip({
                        selector: '[data-toggle="tooltip"]'
                    });
                },
                columnDefs: [
                    { orderable: false, targets: [0, 5] }
                ],
                order: [[1, 'asc']]
            });

            // Select all checkbox
            $('#select-all-table').change(function() {
                if ($(this).is(':checked')) {
                    $('.select-table-row').prop('checked', true);
                } else {
                    $('.select-table-row').prop('checked', false);
                }
                toggleActionButtons();
            });

            // Individual row checkbox
            $('.select-table-row').change(function() {
                if ($('.select-table-row:checked').length == $('.select-table-row').length) {
                    $('#select-all-table').prop('checked', true);
                } else {
                    $('#select-all-table').prop('checked', false);
                }
                toggleActionButtons();
            });

            function toggleActionButtons() {
                if ($('.select-table-row:checked').length > 0) {
                    $('#quick-action-type').prop('disabled', false);
                    $('#quick-action-type').selectpicker('refresh');
                    $('#quick-action-form').css('display', 'flex');
                } else {
                    $('#quick-action-type').prop('disabled', true);
                    $('#quick-action-type').val('');
                    $('#quick-action-type').selectpicker('refresh');
                    $('.status-quick-action').addClass('d-none');
                    $('#quick-action-form').css('display', 'none');
                }
            }

            $('#quick-action-type').change(function() {
                var actionValue = $(this).val();
                if (actionValue != '') {
                    $('#quick-action-apply').removeAttr('disabled');
                    if (actionValue == 'change-status') {
                        $('.status-quick-action').removeClass('d-none');
                    } else {
                        $('.status-quick-action').addClass('d-none');
                    }
                } else {
                    $('#quick-action-apply').attr('disabled', true);
                    $('.status-quick-action').addClass('d-none');
                }
            });

            const applyQuickAction = () => {
                var rowdIds = $("#company-pricing-table input:checkbox:checked").map(function() {
                    return $(this).val();
                }).get();

                var url = "{{ route('pricing.company_pricing.apply_quick_action') }}?row_ids=" + rowdIds;

                $.easyAjax({
                    url: url,
                    container: '#quick-action-form',
                    type: "POST",
                    disableButton: true,
                    buttonSelector: "#quick-action-apply",
                    data: $('#quick-action-form').serialize(),
                    success: function(response) {
                        if (response.status == 'success') {
                            window.location.reload();
                        }
                    }
                })
            };

            $('#quick-action-apply').click(function() {
                const actionValue = $('#quick-action-type').val();
                if (actionValue == 'delete') {
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
                            applyQuickAction();
                        }
                    });
                } else {
                    applyQuickAction();
                }
            });
        });

        $('body').on('change', '.change-pricing-status', function() {
            var id = $(this).data('pricing-id');
            var status = $(this).val();
            var url = "{{ route('pricing.company_pricing.change_status') }}";
            var token = "{{ csrf_token() }}";

            $.easyAjax({
                url: url,
                type: "POST",
                data: {
                    '_token': token,
                    'id': id,
                    'status': status
                },
                success: function(response) {
                    if (response.status == "success") {
                        // Nothing to do, toast already shown by easyAjax
                    }
                }
            });
        });

        $('body').on('click', '.delete-company-pricing', function() {
            var id = $(this).data('row-id');
            var url = "{{ route('pricing.company_pricing.destroy', ':id') }}";
            url = url.replace(':id', id);

            var token = "{{ csrf_token() }}";

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
                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function(response) {
                            if (response.status == 'success') {
                                $('#row-' + id).fadeOut();
                            }
                        }
                    });
                }
            });
        });
    </script>
@endpush
