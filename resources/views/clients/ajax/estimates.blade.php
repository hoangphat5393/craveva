@php
    $addEstimatePermission = user()->permission('add_estimates');
@endphp

<!-- ROW START -->
<div class="row pb-5">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex" id="table-actions">
            @if ($addEstimatePermission == 'all' || $addEstimatePermission == 'added')
                <x-forms.link-primary :link="route('estimates.create') . '?default_client=' . $client->id" class="mr-3 float-left openRightModal" icon="plus" data-redirect-url="{{ route('clients.show', $client->id) . '?tab=estimates' }}">
                    @lang('modules.estimates.createEstimate')
                </x-forms.link-primary>
            @endif
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
<script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>
<script>
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

    $('body').on('click', '.change-status', function() {
        var id = $(this).data('estimate-id');
        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.estimateCancelText')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('messages.confirmCancel')",
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
                var url = "{{ route('estimates.change_status', ':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyBlockUI('#invoices-table');
                window.apiHttp.get(url).then(function(response) {
                    if (response.status == "success") {
                        window.LaravelDataTables["invoices-table"].draw(true);
                    }
                }).catch(function(err) {
                    $.handleApiFormError(err);
                }).finally(function() {
                    $.easyUnblockUI('#invoices-table');
                });
            }
        });
    });

    $('body').on('click', '.delete-table-row', function() {
        var id = $(this).data('estimate-id');
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
                var url = "{{ route('estimates.destroy', ':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyBlockUI('#invoices-table');
                window.apiHttp.delete(url, token).then(function(response) {
                    if (response.status == "success") {
                        showTable();
                    }
                }).catch(function(err) {
                    $.handleApiFormError(err);
                }).finally(function() {
                    $.easyUnblockUI('#invoices-table');
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
        var id = $(this).data('estimate-id');
        var url = "{{ route('estimates.send_estimate', ':id') }}";
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
</script>
