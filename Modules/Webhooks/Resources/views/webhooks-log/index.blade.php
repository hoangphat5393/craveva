@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
    <style>
        #webhooks-log-table .webhook-url-cell { min-width: 320px; word-break: break-all; }
        #webhooks-log-table .request-method-cell { width: 100px; max-width: 100px; text-align: center; }
        #webhooks-log-table .webhook-for-cell { width: 140px; max-width: 140px; }
        #webhooks-log-table .response-code-cell { width: 100px; max-width: 100px; text-align: center; }
        #webhooks-log-table .recorded-on-cell { width: 110px; max-width: 110px; }
        /* Webhook log detail (modal) - contrast & readability */
        .webhook-log-detail .card { border: 1px solid #dee2e6 !important; box-shadow: 0 2px 6px rgba(0,0,0,.1) !important; }
        .webhook-log-detail .card-body { background: #f1f3f5 !important; }
        .webhook-log-detail table { background: #fff; border: 1px solid #dee2e6; border-radius: 4px; overflow: hidden; }
        .webhook-log-detail table td { border-color: #e9ecef; padding: 10px 12px; color: #212529; }
        .webhook-log-detail table tbody tr:nth-child(odd) { background: #fff; }
        .webhook-log-detail table tbody tr:nth-child(even) { background: #f8f9fa; }
        .webhook-log-detail table tbody tr:hover { background: #e9ecef; }
        .webhook-log-detail table td:first-child { font-weight: 600; color: #495057; width: 180px; }
        .webhook-log-detail pre { background: #2d3748 !important; color: #e2e8f0 !important; padding: 16px !important; border-radius: 6px !important; border: 1px solid #4a5568; font-size: 13px; line-height: 1.5; overflow-x: auto; }
    </style>
@endpush

@section('filter-section')
    <x-filters.filter-box>
        <!-- SEARCH START-->
        <div class="task-search d-flex  py-1 pr-lg-3 px-0 border-right-grey align-items-center">
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
        <!-- SEARCH END -->
    </x-filters.filter-box>
@endsection

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                 <x-forms.link-secondary :link="route('webhooks.index')" class="mr-3 float-left" icon="arrow-left">
                    @lang('app.back')
                </x-forms.link-secondary>
            </div>
            <x-datatable.actions>
                <div class="select-status mr-3 pl-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
            </x-datatable.actions>
        </div>
        <!-- Webhook log Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Webhook log End -->
    </div>
    <!-- CONTENT WRAPPER END -->
@endsection

@push('scripts')
    @include('sections.datatable_js')
    <script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>
    <script>
        $('#webhooks-log-table').on('preXhr.dt', function(e, settings, data) {
            var searchText = $('#search-text-field').val();
            data['searchText'] = searchText;
        });
        const showTable = () => {
            window.LaravelDataTables["webhooks-log-table"].draw(true);
        }

        $('#search-text-field').on('keyup', function() {
            if ($('#search-text-field').val() != "") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            }
        });

        $('#quick-action-type').change(function() {
            const actionValue = $(this).val();
            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');
            } else {
                $('#quick-action-apply').attr('disabled', true);
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
            }
        });

        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('log-id');
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
                    var url = "{{ route('webhooks-log.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    window.apiHttp.delete(url, "{{ csrf_token() }}").then(function(response) {
                        if (response.status == "success") {
                            showTable();
                        }
                    }).catch(function(err) { $.handleApiFormError(err); });
                }
            });
        });

        const applyQuickAction = () => {
            var rowIds = $("#webhooks-log-table input:checkbox:checked").map(function() {
                return $(this).val();
            }).get();

            if (rowIds.length === 0) {
                return;
            }

            var url = "{{ route('webhooks-log.apply_quick_action') }}?row_ids=" + rowIds.join(',');

            window.apiHttp.postUrlEncoded(url, $('#quick-action-form').serialize()).then(function(response) {
                if (response.status == 'success') {
                    showTable();
                    if (typeof resetActionButtons === 'function') resetActionButtons();
                    if (typeof deSelectAll === 'function') deSelectAll();
                    $('#quick-action-form').hide();
                }
            }).catch(function(err) { $.handleApiFormError(err); });
        };

        // Copy button for webhook log detail (works in modal)
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.btn-copy-webhook-log');
            if (!btn) return;
            e.preventDefault();
            var targetId = btn.getAttribute('data-copy-target');
            var target = document.querySelector(targetId);
            if (target) {
                var text = target.innerText || target.textContent;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(function() {
                        window.Swal.fire({ icon: 'success', title: '@lang("app.copy")', text: '@lang("messages.copiedToClipboard")', timer: 1500, showConfirmButton: false });
                    });
                } else {
                    var textarea = document.createElement('textarea');
                    textarea.value = text;
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                    window.Swal.fire({ icon: 'success', title: '@lang("app.copy")', text: '@lang("messages.copiedToClipboard")', timer: 1500, showConfirmButton: false });
                }
            }
        });
    </script>
@endpush
