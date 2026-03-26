@php
    $addInvoicesPermission = user()->permission('add_invoices');
@endphp

<!-- ROW START -->
<div class="row pb-5">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex" id="table-actions">
        </div>
        <!-- Add Task Export Buttons End -->
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Task Box End -->
    </div>
</div>

@include('sections.datatable_js')


<script>
    $('#invoices-table').on('preXhr.dt', function(e, settings, data) {

        var clientID = "{{ $client->id }}";
        data['clientID'] = clientID;
    });
    const showTable = () => {
        window.LaravelDataTables["invoices-table"].draw(true);
    }

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
        var id = $(this).data('credit-notes-id');
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
                var url = "{{ route('creditnotes.destroy', ':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                window.apiHttp.delete(url, token).then(function(response) {
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
            $.handleApiFormError(err);
        }).finally(function() {
            $qaBtn.prop('disabled', false);
            $.easyUnblockUI('#quick-action-form');
        });
    };

    $('body').on('click', '.sendButton', function() {
        var id = $(this).data('invoice-id');
        var url = "{{ route('invoices.send_invoice', ':id') }}";
        url = url.replace(':id', id);

        var token = "{{ csrf_token() }}";

        $.easyBlockUI('#invoices-table');
        window.apiHttp.postUrlEncoded(url, '_token=' + encodeURIComponent(token)).then(function(response) {
            if (response.status == "success") {
                window.LaravelDataTables["invoices-table"].draw(true);
            }
        }).catch(function(err) {
            $.handleApiFormError(err);
        }).finally(function() {
            $.easyUnblockUI('#invoices-table');
        });
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
                window.LaravelDataTables["invoices-table"].draw(true);
            }
        }).catch(function(err) {
            $.handleApiFormError(err);
        }).finally(function() {
            $.easyUnblockUI('#invoices-table');
        });
    });

    $('body').on('click', '.credit-notes-upload', function() {
        var creditNoteId = $(this).data('credit-notes-id');
        const url = "{{ route('creditnotes.file_upload') }}?credit_note=" + creditNoteId;
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });
</script>
