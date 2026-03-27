@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
    <x-filters.filter-box>
        <!-- STATUS START -->
        <div class="select-box d-flex py-2 pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">
                @lang('app.status')</p>
            <div class="select-status d-flex">
                <select class="form-control select-picker" name="status" id="status">
                    <option value="all">@lang('app.all')</option>
                    <option value="active">@lang('app.active')</option>
                    <option value="inactive">@lang('app.inactive')</option>
                </select>
            </div>
        </div>
        <!-- SEARCH BY TASK START -->
        <div class="task-search d-flex  py-1 px-lg-3 px-0 border-right-grey align-items-center">
            <form class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
                <div class="input-group bg-grey rounded">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field" placeholder="@lang('app.startTyping')">
                </div>
            </form>
        </div>
        <!-- RESET START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
    </x-filters.filter-box>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if (in_array('admin', user_roles()) || user()->permission('add_pricing_tiers') == 'all' || user()->permission('add_pricing_tiers') == 'added')
                    <x-forms.link-primary :link="route('pricing.tiers.create')" class="mr-3 openRightModal float-left" icon="plus">
                        @lang('pricing::app.addPricingTier')
                    </x-forms.link-primary>
                @endif
                @if (in_array('admin', user_roles()) || user()->permission('add_client_pricing') == 'all' || user()->permission('add_client_pricing') == 'added' || user()->permission('add_pricing_tiers') == 'all' || user()->permission('add_pricing_tiers') == 'added')
                    <x-forms.link-secondary :link="route('pricing.import.index')" class="mr-3 float-left" icon="upload">
                        @lang('app.importExcel')
                    </x-forms.link-secondary>
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
                <div class="select-status mr-3 d-none status-quick-action" id="change-tier-status-bulk">
                    <select name="status" class="form-control select-picker">
                        <option value="active">@lang('app.active')</option>
                        <option value="inactive">@lang('app.inactive')</option>
                    </select>
                </div>
            </x-datatable.actions>
        </div>

        <div class="bg-white rounded mt-3 d-none">
            <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">@lang('pricing::app.menu.tiers')</h4>
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            <table class="table table-hover border-0 w-100" id="pricing-tiers-table">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="select-all-table">
                        </th>
                        <th>ID</th>
                        <th>@lang('app.name')</th>
                        <th>@lang('app.description')</th>
                        <th>@lang('pricing::app.priority')</th>
                        <th>@lang('app.active')</th>
                        <th class="text-right">@lang('app.action')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tiers as $tier)
                        <tr>
                            <td>
                                <input type="checkbox" class="select-table-row" id="datatable-row-{{ $tier->id }}" name="datatable_ids[]" value="{{ $tier->id }}">
                            </td>
                            <td>{{ $tier->id }}</td>
                            <td><a href="{{ route('pricing.tiers.show', $tier->id) }}" class="text-darkest-grey">{{ $tier->name }}</a></td>
                            <td>{{ $tier->description }}</td>
                            <td>{{ $tier->priority }}</td>
                            <td>
                                <select class="form-control select-picker change-tier-status" data-tier-id="{{ $tier->id }}">
                                    <option value="active" data-content="<i class='fa fa-circle text-light-green'></i> @lang('app.active')" {{ $tier->is_active ? 'selected' : '' }}>
                                        @lang('app.active')
                                    </option>
                                    <option value="inactive" data-content="<i class='fa fa-circle text-red'></i> @lang('app.inactive')" {{ !$tier->is_active ? 'selected' : '' }}>
                                        @lang('app.inactive')
                                    </option>
                                </select>
                            </td>
                            <td class="text-right">
                                <div class="task_view">
                                    <div class="dropdown">
                                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link" id="dropdownMenuLink-{{ $tier->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="icon-options-vertical icons"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-{{ $tier->id }}" tabindex="0">
                                            <a class="dropdown-item" href="{{ route('pricing.tiers.show', $tier->id) }}">
                                                <i class="fa fa-eye mr-2"></i> @lang('app.view')
                                            </a>
                                            <a class="dropdown-item openRightModal" href="{{ route('pricing.tiers.edit', $tier->id) }}">
                                                <i class="fa fa-edit mr-2"></i> @lang('app.edit')
                                            </a>
                                            <a class="dropdown-item delete-table-row" href="javascript:;" data-id="{{ $tier->id }}">
                                                <i class="fa fa-trash mr-2"></i> @lang('app.delete')
                                            </a>
                                        </div>
                                    </div>
                                </div>
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
        $(document).ready(function() {
            var table = $('#pricing-tiers-table').DataTable({
                "ordering": true,
                "searching": false, // We use custom search
                "paging": false, // Disable paging as we're showing all
                "info": false, // Disable info
                "columnDefs": [{
                        "orderable": false,
                        "targets": [0, 6]
                    }, // Disable sorting on Checkbox and Action columns
                    {
                        "orderable": false,
                        "targets": "no-sort"
                    }
                ],
                "order": [
                    [1, "desc"]
                ] // Default sort by ID desc
            });

            function filterTable() {
                var status = $('#status').val();
                var searchText = $('#search-text-field').val().toLowerCase();

                $('tbody tr').each(function() {
                    var row = $(this);
                    var rowStatus = row.find('.change-tier-status').val();
                    var rowName = row.find('td:eq(2)').text().trim().toLowerCase();
                    var rowDesc = row.find('td:eq(3)').text().trim().toLowerCase();

                    var statusMatch = (status === 'all') ||
                        (status === 'active' && rowStatus === 'active') ||
                        (status === 'inactive' && rowStatus === 'inactive');

                    var searchMatch = (searchText === '') ||
                        (rowName.includes(searchText) || rowDesc.includes(searchText));

                    if (statusMatch && searchMatch) {
                        row.show();
                    } else {
                        row.hide();
                    }
                });

                if (status !== 'all' || searchText !== '') {
                    $('#reset-filters').removeClass('d-none');
                } else {
                    $('#reset-filters').addClass('d-none');
                }
            }

            $('#status').on('change', filterTable);
            $('#search-text-field').on('keyup', filterTable);

            $('#reset-filters').click(function() {
                $('#status').val('all').selectpicker('refresh');
                $('#search-text-field').val('');
                filterTable();
            });

            // Initialize Select2/Picker if needed (though select-picker class usually handled by layout)
            // $('.select-picker').selectpicker();

            $('#select-all-table').change(function() {
                var isChecked = $(this).prop('checked');
                $('.select-table-row').prop('checked', isChecked);
                toggleQuickAction();
            });

            $('body').on('change', '.select-table-row', function() {
                toggleQuickAction();
            });

            function toggleQuickAction() {
                var checkedCount = $('.select-table-row:checked').length;
                if (checkedCount > 0) {
                    $('#quick-action-type').prop('disabled', false);
                    $('#quick-action-type').selectpicker('refresh');
                    $('#quick-action-form').css('display', 'flex');
                } else {
                    $('#quick-action-type').prop('disabled', true);
                    $('#quick-action-type').val('');
                    $('#quick-action-type').selectpicker('refresh');
                    $('#quick-action-form').css('display', 'none');
                }
            }

            $('#quick-action-type').change(function() {
                var actionValue = $(this).val();
                if (actionValue != '') {
                    $('#quick-action-apply').removeAttr('disabled');

                    if (actionValue == 'change-status') {
                        $('.status-quick-action').addClass('d-none');
                        $('#change-tier-status-bulk').removeClass('d-none');
                    } else {
                        $('.status-quick-action').addClass('d-none');
                    }

                } else {
                    $('#quick-action-apply').attr('disabled', true);
                    $('.status-quick-action').addClass('d-none');
                }
            });

            $('#quick-action-apply').click(function() {
                var actionValue = $('#quick-action-type').val();
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
                        } else {
                            $('#quick-action-type').val('');
                            $('#quick-action-type').selectpicker('refresh');
                        }
                    });
                } else if (actionValue == 'change-status') {
                    applyQuickAction();
                } else {
                    applyQuickAction();
                }
            });

            function applyQuickAction() {
                var rowdIds = $(".select-table-row:checked").map(function() {
                    return $(this).val();
                }).get();

                var url = "{{ route('pricing.tiers.apply_quick_action') }}";

                window.apiHttp.postUrlEncoded(url, $('#quick-action-form').serialize() + "&row_ids=" + rowdIds.join(','))
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

        $('body').on('change', '.change-tier-status', function() {
            var id = $(this).data('tier-id');
            var url = "{{ route('pricing.tiers.change_status') }}";
            var token = "{{ csrf_token() }}";
            var status = $(this).val();

            if (typeof id !== 'undefined') {
                window.apiHttp.postUrlEncoded(url, {
                    '_token': token,
                    tierId: id,
                    status: status
                })
                    .then(function(response) {
                        if (response.status == "success") {
                            // window.location.reload();
                        }
                    })
                    .catch(function(err) {
                        $.handleApiFormError(err);
                    });
            }
        });

        $('body').on('click', '.delete-table-row', function() {
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
                    var url = "{{ route('pricing.tiers.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    window.apiHttp.delete(url, "{{ csrf_token() }}")
                        .then(function(response) {
                            if (response.status == "success") {
                                window.location.reload();
                            }
                        })
                        .catch(function(err) {
                            $.handleApiFormError(err);
                        });
                }
            });
        });
    </script>
@endpush
