@php
$addExpensesPermission = user()->permission('add_expenses');
@endphp

<!-- ROW START -->
<div class="row py-3 py-lg-5 py-md-5">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex justify-content-between">
            <div id="table-actions" class="align-items-center">
                @if (($addExpensesPermission == 'all' || $addExpensesPermission == 'added') && !$project->trashed())
                    <x-forms.link-primary :link="route('expenses.create').'?project_id='.$project->id" class="mr-3 float-left openRightModal"
                        icon="plus" data-redirect-url="{{ url()->full() }}">
                        @lang('modules.expenses.addExpense')
                    </x-forms.link-primary>
                @endif
            </div>

        </div>


        <form action="" id="filter-form">
            <div class="d-block d-lg-flex d-md-flex my-3">
                <!-- STATUS START -->
                <div class="select-box py-2 px-0 mr-3">
                    <x-forms.label :fieldLabel="__('app.status')" fieldId="status" />
                    <select class="form-control select-picker" id="filter-status">
                        <option value="all">@lang('app.all')</option>
                        <option value="pending">@lang('app.pending')</option>
                        <option value="approved">@lang('app.approved')</option>
                        <option value="rejected">@lang('app.rejected')</option>
                    </select>
                </div>
                <!-- STATUS END -->

                <!-- SEARCH BY TASK START -->
                <div class="select-box py-2 px-lg-2 px-md-2 px-0 mr-3">
                    <x-forms.label fieldId="status" class="d-none d-lg-block d-md-block" />
                    <div class="input-group bg-grey rounded">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-additional-grey">
                                <i class="fa fa-search f-13 text-dark-grey"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control f-14 p-1 height-35 border" id="search-text-field"
                            placeholder="@lang('app.startTyping')">
                    </div>
                </div>
                <!-- SEARCH BY TASK END -->

                <!-- RESET START -->
                <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 mt-4">
                    <x-forms.button-secondary class="btn-xs d-none height-35 mt-2" id="reset-filters" icon="times-circle">
                        @lang('app.clearFilters')
                    </x-forms.button-secondary>
                </div>
                <!-- RESET END -->
            </div>
        </form>


        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

            <x-datatable.actions>
                <div class="select-status mr-3 pl-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="change-status">@lang('modules.tasks.changeStatus')</option>
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
                <div class="select-status mr-3 d-none quick-action-field" id="change-status-action">
                    <select name="status" class="form-control select-picker">
                        <option value="deactive">@lang('app.inactive')</option>
                        <option value="active">@lang('app.active')</option>
                    </select>
                </div>
            </x-datatable.actions>

        </div>
        <!-- Task Box End -->
    </div>
</div>

@include('sections.datatable_js')


<script>
    $('#expenses-table').on('preXhr.dt', function(e, settings, data) {

        var status = $('#filter-status').val();
        var searchText = $('#search-text-field').val();
        var employee = $('#employee2').val();


        data['status'] = status;
        data['employee'] = employee;
        data['projectId'] = "{{ $project->id }}";
        data['searchText'] = searchText;
    });
    const showTable = () => {
        window.LaravelDataTables["expenses-table"].draw(true);
    }

    $('#filter-status, #employee2').on('change keyup',
        function() {
            if ($('#filter-status').val() != "all") {
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

    $('#reset-filters').click(function() {
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

    $('body').on('change', '.change-expense-status', function() {
        var id = $(this).data('expense-id');
        var url = "{{ route('expenses.change_status') }}";

        var token = "{{ csrf_token() }}";
        var status = $(this).val();

        if (typeof id !== 'undefined') {
            window.apiHttp.postUrlEncoded("{{ route('expenses.change_status') }}", {
                _token: token,
                expenseId: id,
                status: status
            }).then(function(response) {
                if (response.status == "success") {
                    window.LaravelDataTables["expenses-table"].draw(true);
                }
            }).catch(function(err) {
                $.handleApiFormError(err);
            });
        }
    });

    $('body').on('click', '.delete-table-row', function() {
        var id = $(this).data('expense-id');
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
                var url = "{{ route('expenses.destroy', ':id') }}";
                url = url.replace(':id', id);

                window.apiHttp.delete(url, "{{ csrf_token() }}").then(function(response) {
                    if (response.status == "success") {
                        showTable();
                    }
                }).catch(function(err) {
                    $.handleApiFormError(err);
                });
            }
        });
    });

    const applyQuickAction = () => {
        var rowdIds = $("#expenses-table input:checkbox:checked").map(function() {
            return $(this).val();
        }).get();

        var url = "{{ route('expenses.apply_quick_action') }}?row_ids=" + rowdIds;

        var $qaBtn = $('#quick-action-form').find('#quick-action-apply');
        var qaPrev = $qaBtn.html();
        $qaBtn.attr('data-prev-text', qaPrev);
        $qaBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + (document.loading || 'Loading...'));
        $.easyBlockUI('#quick-action-form');
        window.apiHttp.postUrlEncoded(url, $('#quick-action-form').serialize()).then(function(response) {
            if (response.status == 'success') {
                showTable();
                resetActionButtons();
            }
        }).catch(function(err) {
            $.handleApiFormError(err);
        }).finally(function() {
            $.easyUnblockUI('#quick-action-form');
            $qaBtn.html($qaBtn.attr('data-prev-text'));
            $qaBtn.prop('disabled', false);
        });
    };

</script>
