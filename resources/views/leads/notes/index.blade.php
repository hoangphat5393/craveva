@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')

    <x-filters.filter-box>

        <!-- SEARCH BY TASK START -->
        <div class="task-search d-flex  py-1 px-lg-3 px-0 border-right-grey align-items-center">
            <form class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
                <div class="input-group bg-grey rounded">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field"
                        placeholder="@lang('app.startTyping')">
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
    </x-filters.filter-box>

@endsection

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex" id="table-actions">

        </div>
        <!-- Add Task Export Buttons End -->
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

            <x-datatable.actions>
                <div class="select-status mr-3 pl-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
            </x-datatable.actions>

        </div>
        <!-- Task Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->

@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#client-notes-table').on('preXhr.dt', function(e, settings, data) {
            var searchText = $('#search-text-field').val();
            var clientId = '{{ user()->id }}';

            data['clientID'] = clientId;
            data['searchText'] = searchText;
        });

        const showTable = () => {
            window.LaravelDataTables["client-notes-table"].draw(true);
        }

        $('#search-text-field').on('change keyup', function() {
            if ($('#search-text-field').val() != "") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            } else {
                $('#reset-filters').addClass('d-none');
                showTable();
            }
        });

        $('#reset-filters').click(function() {
            $('#filter-form')[0].reset();
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
        var id = $(this).data('user-id');
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
                var url = "{{ route('client-notes.destroy', ':id') }}";
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
        var rowdIds = $("#client-notes-table input:checkbox:checked").map(function() {
            return $(this).val();
        }).get();

        var url = "{{ route('client-notes.apply_quick_action') }}?row_ids=" + rowdIds;

        var $qaBtn = $('#quick-action-form').find('#quick-action-apply');
        var qaPrev = $qaBtn.html();
        $qaBtn.attr('data-prev-text', qaPrev);
        $qaBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + (document.loading || 'Loading...'));
        $.easyBlockUI('#quick-action-form');
        window.apiHttp.postUrlEncoded(url, $('#quick-action-form').serialize()).then(function(response) {
            if (response.status == 'success') {
                showTable();
                resetActionButtons();
                deSelectAll();
            }
        }).catch(function(err) {
            $.handleApiFormError(err);
        }).finally(function() {
            $.easyUnblockUI('#quick-action-form');
            $qaBtn.html($qaBtn.attr('data-prev-text'));
            $qaBtn.prop('disabled', false);
        });
    };

    $('body').on('click', '.ask-for-password', function() {
        let clientNoteId = $(this).data('client-note-id');

        var url = "{{ route('client_notes.ask_for_password', ':id') }}";
        url = url.replace(':id', clientNoteId);

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);

    });

    // show note detail in right modal
    var getNoteDetail = function(id) {
        openTaskDetail();

        var url = "{{ route('client-notes.show', ':id') }}";
        url = url.replace(':id', id);

        if (typeof historyPush === 'function') {
            historyPush(url);
        }
        $.easyBlockUI(RIGHT_MODAL);
        window.apiHttp.get(url).then(function(response) {
            if (response.status == "success") {
                $(RIGHT_MODAL_CONTENT).html(response.html);
                $(RIGHT_MODAL_TITLE).html(response.title);
            }
        }).catch(function(err) {
            if (err.status === 403) {
                $(RIGHT_MODAL_CONTENT).html(
                    '<div class="align-content-between d-flex justify-content-center mt-105 f-21">403 | Permission Denied</div>'
                );
            } else if (err.status === 404) {
                $(RIGHT_MODAL_CONTENT).html(
                    '<div class="align-content-between d-flex justify-content-center mt-105 f-21">404 | Not Found</div>'
                );
            } else if (err.status === 500) {
                $(RIGHT_MODAL_CONTENT).html(
                    '<div class="align-content-between d-flex justify-content-center mt-105 f-21">500 | Something Went Wrong</div>'
                );
            } else {
                $.handleApiFormError(err);
            }
        }).finally(function() {
            $.easyUnblockUI(RIGHT_MODAL);
        });
    };


    </script>
@endpush
