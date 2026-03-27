@extends('layouts.app')
@section('content')
    <!-- SETTINGS START -->
    <div class="w-100 d-flex">

        <x-super-admin.front-setting-sidebar :activeMenu="$activeSettingMenu"/>

        <x-setting-card>

            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <h2 class="p-20 mb-0 f-21 font-weight-normal text-capitalize border-bottom-grey">
                        @lang($pageTitle)</h2>
                </div>
            </x-slot>
            <x-slot name="buttons">
                <div class="row">
                    <div class="mb-2 col-md-12">
                        <x-forms.button-primary class="mr-3" icon="plus" id="addWidiget"> @lang('app.addNew')
                        </x-forms.button-primary>
                    </div>
                </div>
            </x-slot>

            <!-- LEAVE SETTING START -->
            <div class="p-0 col-lg-12 col-md-12 ntfcn-tab-content-left w-100">

                <x-table class="table mb-0 table-sm-responsive">
                    <x-slot name="thead">
                        <th>#</th>
                        <th>@lang('superadmin.frontCms.widgetName')</th>
                        <th class="text-right">@lang('app.action')</th>
                    </x-slot>

                    @forelse($frontWidgets as $widget)
                        <tr class="dataRow{{ $widget->id }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $widget->name }}</td>
                            <td class="text-right">
                                <div class="task_view">
                                    <a class="task_view_more d-flex align-items-center justify-content-center edit-channel"
                                       data-id="{{ $widget->id }}" href="javascript:;">
                                        <i class="mr-2 fa fa-edit icons"></i> @lang('app.edit')
                                    </a>
                                </div>
                                <div class="mt-1 task_view mt-lg-0 mt-md-0">
                                    <a class="task_view_more d-flex align-items-center justify-content-center delete-table-row"
                                       href="javascript:;" data-id="{{ $widget->id }}">
                                        <i class="mr-2 fa fa-trash icons"></i> @lang('app.delete')
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-cards.no-record-found-list colspan="3"/>
                    @endforelse

                </x-table>

            </div>
            <!-- LEAVE SETTING END -->
        </x-setting-card>

    </div>
    <!-- SETTINGS END -->
@endsection

@push('scripts')
    <script src="{{ asset('vendor/ace/ace.js') }}"></script>
    <script src="{{ asset('vendor/ace/theme-twilight.js') }}"></script>
    <script src="{{ asset('vendor/ace/mode-css.js') }}"></script>
    <script src="{{ asset('vendor/ace/jquery-ace.min.js') }}"></script>
    <script>
        /* open add front client modal */
        $('body').on('click', '#addWidiget', function () {
            var url = "{{ route('superadmin.front-settings.front-widgets.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        /* open add front client modal */
        $('body').on('click', '.edit-channel', function () {
            var id = $(this).data('id');
            var url = "{{ route('superadmin.front-settings.front-widgets.edit', [':id']) }}";
            url = url.replace(':id', id);

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('body').on('click', '.delete-table-row', function () {
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
                    var url = "{{ route('superadmin.front-settings.front-widgets.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyBlockUI();
                    window.apiHttp.delete(url, token)
                        .then(function (response) {
                            if (response.status == "success") {
                                $('.dataRow' + id).fadeOut('normal', function () {
                                    $(this).remove();

                                    if ($("[class*=dataRow]").length == 0) {
                                        location.reload();
                                    }
                                });
                            }
                        })
                        .catch(function (err) { $.handleApiFormError(err); })
                        .finally(function () { $.easyUnblockUI(); });
                }
            });
        });

        $("body").on("click", "#save-front-widget", function (event) {
            const $btn = $('#save-front-widget');
            const prev = $btn.html();
            $.easyBlockUI('#createFrontWidget');
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + (document.loading || 'Loading...'));
            window.apiHttp.postUrlEncoded("{{ route('superadmin.front-settings.front-widgets.store') }}", $('#createFrontWidget').serialize())
                .then(function (response) {
                    if (response.status === 'success') {
                        if (response.action === 'redirect' && response.url) {
                            window.location.href = response.url;
                        } else if (typeof response.message !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                text: response.message,
                                toast: true,
                                position: 'top-end',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                customClass: { confirmButton: 'btn btn-primary' },
                                showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' }
                            });
                        }
                    }
                })
                .catch(function (err) { $.handleApiFormError(err); })
                .finally(function () {
                    $.easyUnblockUI('#createFrontWidget');
                    $btn.prop('disabled', false).html(prev);
                });
        });

    </script>
@endpush
