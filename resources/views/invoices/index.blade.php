@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

<style>
    div.status-cell {
        width: 200px;
    }
</style>

@section('filter-section')

    <x-filters.filter-box>
        <!-- DATE START -->
        <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
            <div class="select-status d-flex">
                <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey" id="datatableRange" placeholder="@lang('placeholders.dateRange')">
            </div>
        </div>
        <!-- DATE END -->

        @if (!in_array('client', user_roles()) && in_array('clients', user_modules()))
            <!-- CLIENT START -->
            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.client')</p>
                <div class="select-status">
                    <select class="form-control select-picker" id="clientID" data-live-search="true" data-size="8">
                        @if (!in_array('client', user_roles()))
                            <option value="all">@lang('app.all')</option>
                        @endif
                        @foreach ($clients as $client)
                            {{-- remote search appends options progressively --}}
                            <x-user-option :user="$client" />
                        @endforeach
                    </select>
                </div>
            </div>
            <!-- CLIENT END -->
        @endif

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
        <!-- SEARCH BY TASK END -->

        <!-- RESET START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->

        <!-- MORE FILTERS START -->
        <x-filters.more-filter-box>
            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('app.project')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="project_id" id="filter_project_id" data-container="body" data-live-search="true" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('app.status')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="status" id="status" data-live-search="true" data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            <option {{ request('status') == 'pending' ? 'selected' : '' }} value="pending">
                                @lang('app.pending')</option>
                            <option {{ request('status') == 'unpaid' ? 'selected' : '' }} value="unpaid">
                                @lang('app.unpaid')</option>
                            <option {{ request('status') == 'paid' ? 'selected' : '' }} value="paid">@lang('app.paid')
                            </option>
                            <option {{ request('status') == 'partial' ? 'selected' : '' }} value="partial">
                                @lang('app.partial')</option>
                            <option {{ request('status') == 'canceled' ? 'selected' : '' }} value="canceled">
                                @lang('app.canceled')</option>
                            <option {{ request('status') == 'pending-confirmation' ? 'selected' : '' }} value="pending-confirmation">
                                @lang('app.pendingConfirmation')</option>
                        </select>
                    </div>
                </div>
            </div>


        </x-filters.more-filter-box>
        <!-- MORE FILTERS END -->

    </x-filters.filter-box>

@endsection

@php
    $addInvoicesPermission = user()->permission('add_invoices');
    $manageRecurringInvoicesPermission = user()->permission('manage_recurring_invoice');
@endphp

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Task Export Buttons Start -->
        <div class="d-block d-lg-flex d-md-flex justify-content-between">
            <div id="table-actions" class="flex-grow-1 align-items-center mb-2 mb-lg-0 mb-md-0">
                @if ($addInvoicesPermission == 'all')
                    <x-forms.link-primary :link="route('invoices.create')" class="mr-3 float-left mb-2 mb-lg-0 mb-md-0" icon="plus">
                        @lang('modules.invoices.addInvoice')
                    </x-forms.link-primary>
                @endif
                @if ($addInvoicesPermission == 'all' || $manageRecurringInvoicesPermission == 'all')
                    <x-forms.link-secondary class="mr-3 float-left mb-2 mb-lg-0 mb-md-0" icon="redo" :link="route('recurring-invoices.index')">
                        @lang('app.invoiceRecurring')
                    </x-forms.link-secondary>
                @endif
                @if ($addInvoicesPermission == 'all' && in_array('projects', user_modules()))
                    <x-forms.link-secondary class="mr-3 float-left mb-2 mb-lg-0 mb-md-0" icon="plus" :link="route('invoices.create', ['type' => 'timelog'])">
                        @lang('app.createTimeLogInvoice')
                    </x-forms.link-secondary>
                @endif

            </div>

            <div class="btn-group mt-3 mt-lg-0 mt-md-0 ml-lg-3 d-none d-lg-block" role="group">
                <a href="javascript:;" class="img-lightbox btn btn-secondary f-14" data-image-url="{{ asset('img/invoice-lc.png') }}" data-toggle="tooltip" data-original-title="@lang('app.howItWorks')"><i class="side-icon bi bi-question-circle"></i></a>
            </div>

        </div>

        <!-- Add Task Export Buttons End -->
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white w-100 table-responsive">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Task Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->
@endsection

@push('scripts')
    @include('sections.datatable_js')
    <script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>
    <script>
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

        function initInvoiceClientFilterRemoteSearch() {
            var $select = $('#clientID');
            if (!$select.length) {
                return;
            }

            var state = {
                term: '',
                page: 1,
                hasMore: true,
                loading: false,
                requestId: 0
            };

            function selectedValue() {
                return $select.val() || 'all';
            }

            function optionHtml(item, selected) {
                var label = escapeHtml(item.name || '');
                var company = item.company_name ? ' - ' + escapeHtml(item.company_name) : '';
                var email = item.email ? ' (' + escapeHtml(item.email) + ')' : '';
                var isSelected = String(selected) === String(item.id);
                return '<option value="' + item.id + '"' + (isSelected ? ' selected' : '') + ' data-content="' + label + company + email + '">' + label + '</option>';
            }

            function replaceOptions(items) {
                var selected = selectedValue();
                var html = '<option value="all">{{ __('app.all') }}</option>';
                $.each(items, function(_, item) {
                    html += optionHtml(item, selected);
                });
                $select.html(html);
                $select.selectpicker('refresh');
            }

            function appendOptions(items) {
                var selected = selectedValue();
                $.each(items, function(_, item) {
                    if ($select.find('option[value="' + item.id + '"]').length) {
                        return;
                    }
                    $select.append(optionHtml(item, selected));
                });
                $select.selectpicker('refresh');
            }

            function load(term, page, appendMode) {
                if (state.loading) {
                    return;
                }

                state.loading = true;
                var currentRequestId = ++state.requestId;

                window.apiHttp.get("{{ route('invoices.search_clients') }}", {
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

        $(function() {
            var clipboard = new ClipboardJS('.btn-copy');

            clipboard.on('success', function(e) {
                Swal.fire({
                    icon: 'success',
                    text: '@lang('app.copied')',
                    toast: true,
                    position: 'top-end',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    customClass: {
                        confirmButton: 'btn btn-primary',
                    },
                    showClass: {
                        popup: 'swal2-noanimation',
                        backdrop: 'swal2-noanimation'
                    },
                })
            });
        });

        $('#invoices-table').on('preXhr.dt', function(e, settings, data) {

            var dateRangePicker = $('#datatableRange').data('daterangepicker');
            var startDate = $('#datatableRange').val();

            if (startDate == '') {
                startDate = null;
                endDate = null;
            } else {
                startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
            }

            var projectID = $('#filter_project_id').val();
            if (!projectID) {
                projectID = 0;
            }
            var clientID = $('#clientID').val();
            var status = $('#status').val();

            var searchText = $('#search-text-field').val();

            data['clientID'] = clientID;
            data['projectID'] = projectID;
            data['status'] = status;
            data['startDate'] = startDate;
            data['endDate'] = endDate;
            data['searchText'] = searchText;
        });

        const showTable = () => {
            window.LaravelDataTables["invoices-table"].draw(true);
        }

        $('#clientID, #filter_project_id, #status')
            .on('change keyup',
                function() {
                    if ($('#filter_project_id').val() != "all") {
                        $('#reset-filters').removeClass('d-none');
                        showTable();
                    } else if ($('#status').val() != "all") {
                        $('#reset-filters').removeClass('d-none');
                        showTable();
                    } else if ($('#clientID').val() != "all") {
                        $('#reset-filters').removeClass('d-none');
                        showTable();
                    } else {
                        $('#reset-filters').addClass('d-none');
                        showTable();
                    }
                });

        $('#search-text-field').on('keyup', function() {
            if ($('#search-text-field').val() != "") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            }
        });

        $('#reset-filters,#reset-filters-2').click(function() {
            $('#filter-form')[0].reset();

            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });


        $('#quick-action-type').change(function() {
            const actionValue = $(this).val();
            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');

                if (actionValue == 'change-status') {
                    $('.quick-action-field').addClass('d-none');
                    $('#change-status-action').removeClass('d-none');
                } else {
                    $('.quick-action-field').addClass('d-none');
                }
            } else {
                $('#quick-action-apply').attr('disabled', true);
                $('.quick-action-field').addClass('d-none');
            }
        });

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

        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('invoice-id');
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
                    var url = "{{ route('invoices.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyBlockUI('#invoices-table');
                    window.apiHttp.delete(url, token).then(function(response) {
                        if (response.status == "success") {
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
                        $.easyUnblockUI('#invoices-table');
                    });
                }
            });
        });

        $('body').on('click', '.unpaidAndPartialPaidCreditNote', function() {
            var id = $(this).data('invoice-id');

            Swal.fire({
                title: "@lang('messages.confirmation.createCreditNotes')",
                text: "@lang('messages.creditText')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('app.yes')",
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
                    var url = "{{ route('creditnotes.create') }}?invoice=:id";
                    url = url.replace(':id', id);

                    location.href = url;
                }
            });
        });

        const applyQuickAction = () => {
            var rowdIds = $("#invoices-table input:checkbox:checked").map(function() {
                return $(this).val();
            }).get();

            var url = "{{ route('invoices.apply_quick_action') }}?row_ids=" + rowdIds;

            var $qaBtn = $("#quick-action-apply");
            $qaBtn.prop('disabled', true);
            $.easyBlockUI('#quick-action-form');
            window.apiHttp.postUrlEncoded(url, $('#quick-action-form').serialize()).then(function(response) {
                if (response.status == 'success') {
                    showTable();
                    resetActionButtons();
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
                $qaBtn.prop('disabled', false);
                $.easyUnblockUI('#quick-action-form');
            });
        };

        $('body').on('click', '.approveButton', function() {
            var id = $(this).data('invoice-id');
            var url = "{{ route('invoices.approve_offline_invoice', ':id') }}";
            url = url.replace(':id', id);

            var token = "{{ csrf_token() }}";
            $.easyBlockUI('#invoices-table');
            window.apiHttp.post(url, {
                _token: token
            }).then(function(response) {
                if (response.status == "success") {
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
                $.easyUnblockUI('#invoices-table');
            });
        });

        $('body').on('click', '.sendButton', function() {
            var id = $(this).data('invoice-id');
            var dataType = $(this).data('type');
            var invoiceAmt = $(this).data('amt');
            var url = "{{ route('invoices.send_invoice', ':id') }}";
            url = url.replace(':id', id);

            var token = "{{ csrf_token() }}";

            if (invoiceAmt == 0 && invoiceAmt != null) {
                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')",
                    text: "@lang('messages.markAsPaid')",
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "@lang('app.yes')",
                    cancelButtonText: "@lang('app.no')",
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

                        $.easyBlockUI('#invoices-table');
                        window.apiHttp.post(url, {
                            _token: token,
                            data_type: dataType
                        }).then(function(response) {
                            if (response.status == "success") {
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
                            $.easyUnblockUI('#invoices-table');
                        });
                    }
                });
            } else {
                $.easyBlockUI('#invoices-table');
                window.apiHttp.post(url, {
                    _token: token,
                    data_type: dataType
                }).then(function(response) {
                    if (response.status == "success") {
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
                    $.easyUnblockUI('#invoices-table');
                });
            }


        });

        $('body').on('click', '.reminderButton', function() {
            var id = $(this).data('invoice-id');
            var url = "{{ route('invoices.payment_reminder', ':id') }}";
            url = url.replace(':id', id);

            var token = "{{ csrf_token() }}";

            $.easyBlockUI('#invoices-table');
            window.apiHttp.get(url).then(function(response) {
                if (response.status == "success") {
                    $.unblockUI();
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
                $.easyUnblockUI('#invoices-table');
            });
        });

        $('body').on('click', '.invoice-upload', function() {
            var invoiceId = $(this).data('invoice-id');
            const url = "{{ route('invoices.file_upload') }}?invoice_id=" + invoiceId;
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('body').on('click', '#recurring-invoice', function() {
            window.location.href = "{{ route('recurring-invoices.index') }} ";
        });


        $('body').on('click', '.cancel-invoice', function() {
            var id = $(this).data('invoice-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.invoiceText')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('app.yes')",
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

                    var url = "{{ route('invoices.update_status', ':id') }}";
                    url = url.replace(':id', id);

                    $.easyBlockUI('#invoices-table');
                    window.apiHttp.get(url).then(function(response) {
                        if (response.status == "success") {
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
                        $.easyUnblockUI('#invoices-table');
                    });
                }
            });
        });

        $('body').on('click', '.toggle-shipping-address', function() {
            let invoiceId = $(this).data('invoice-id');

            let url = "{{ route('invoices.toggle_shipping_address', ':id') }}";
            url = url.replace(':id', invoiceId);

            $.easyBlockUI('#invoices-table');
            window.apiHttp.get(url).then(function(response) {
                if (response.status === 'success') {
                    window.LaravelDataTables["invoices-table"].draw(true);
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
                $.easyUnblockUI('#invoices-table');
            });

        });

        $('body').on('click', '.add-shipping-address', function() {
            let invoiceId = $(this).data('invoice-id');

            var url = "{{ route('invoices.shipping_address_modal', [':id']) }}";
            url = url.replace(':id', invoiceId);

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $(document).ready(function() {
            initInvoiceClientFilterRemoteSearch();

            @if (!is_null(request('start')) && !is_null(request('end')))
                $('#datatableRange').val('{{ request('start') }}' +
                    ' @lang('app.to') ' + '{{ request('end') }}');
                $('#datatableRange').data('daterangepicker').setStartDate("{{ request('start') }}");
                $('#datatableRange').data('daterangepicker').setEndDate("{{ request('end') }}");
                showTable();
            @endif
        });
    </script>
@endpush
