@php
    $recruitSettingsPermission = user()->permission('recruit_settings');
@endphp

<div class="table-responsive p-20">
    <div id="table-actions" class="d-block d-lg-flex align-items-center">

        @if ($recruitSettingsPermission == 'all')
            <x-forms.button-primary icon="plus" id="addQuestion" class="mb-2">
                @lang('app.add') @lang('recruit::modules.setting.question')
            </x-forms.button-primary>
        @endif

    </div>
    <x-table class="table-bordered">
        <x-slot name="thead">
            <th>@lang('recruit::modules.setting.question')</th>
            <th>@lang('modules.tasks.category')</th>
            <th>@lang('modules.invoices.type')</th>
            <th>@lang('app.required')</th>
            <th>@lang('app.status')</th>
            <th class="text-right">@lang('app.action')</th>
        </x-slot>
        @forelse($jobQuestions as $question)
            <tr class="row{{ $question->id }}">
                <td class="col-md-6">
                    {{ ucwords($question->question) }}
                </td>
                <td>
                    @if ($question->category == 'job_application')
                        @lang('recruit::app.report.jobapplication')
                    @else
                        @lang('recruit::app.menu.joboffer')
                    @endif
                </td>
                <td>
                    {{ ucwords($question->type) }}
                </td>
                <td>
                    @if ($question->required == 'yes')
                        <span class="badge  badge-danger disabled color-palette">{{ ucwords($question->required) }}</span>
                    @else
                        <span class="badge badge-secondary disabled color-palette">{{ ucwords($question->required) }}</span>
                    @endif
                </td>

                <td>
                    @if ($recruitSettingsPermission == 'all')
                        <select class="change-question-status form-control select-picker" data-question-id="{{ $question->id }}">
                            <option value="enable" @selected($question->status == 'enable')>@lang('app.enable')</option>
                            <option value="disable" @selected($question->status == 'disable')>@lang('app.disable')</option>
                        </select>
                    @else
                        @if ($question->status == 'enable')
                            <i class="fa fa-circle mr-1 text-light-green f-10"></i>@lang($question->status)
                        @else
                            <i class="fa fa-circle mr-1 text-red f-10"></i>@lang($question->status)
                        @endif
                    @endif
                </td>
                <td class="text-right col-md-2">
                    <div class="task_view">
                        @if ($recruitSettingsPermission == 'all')
                            <a href="javascript:;" data-question-id="{{ $question->id }}" class="editQuestion task_view_more d-flex align-items-center justify-content-center">
                                <i class="fa fa-edit icons mr-1"></i> @lang('app.edit')
                            </a>
                        @endif
                    </div>
                    <div class="task_view">
                        @if ($recruitSettingsPermission == 'all')
                            <a href="javascript:;" data-question-id="{{ $question->id }}" class="delete-question task_view_more d-flex align-items-center justify-content-center">
                                <i class="fa fa-trash icons mr-2"></i> @lang('app.delete')
                            </a>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <x-cards.no-record-found-list :colspan="6" />
        @endforelse
    </x-table>
</div>

<script>
    /* delete link */
    $('body').off('click', ".delete-question").on('click', '.delete-question', function() {

        var id = $(this).data('question-id');
        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.confirmRemove')",
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
                var url = "{{ route('custom-question-settings.destroy', ':id') }}";
                url = url.replace(':id', id);

                window.apiHttp.delete(url, {
                    _token: "{{ csrf_token() }}"
                }).then(function(response) {
                    if (response.status == "success") {
                        if (typeof $.showApiSuccessToast === 'function') {
                            $.showApiSuccessToast(response.message || '');
                        }
                        if (typeof window.refreshRecruitSettingsTab === 'function') {
                            window.refreshRecruitSettingsTab('recruit-custom-question-setting');
                        } else {
                            window.location.reload();
                        }
                    }
                }).catch(function(err) {
                    $.handleApiFormError(err);
                });
            }
        });
    });

    /* change links status */
    $('body').off('change', '.change-question-status').on('change', '.change-question-status', function() {
        var questionId = $(this).data('question-id');
        var status = $(this).val();
        var url = "{{ route('custom-question-settings.change_status') }}";

        if (typeof questionId !== 'undefined') {
            window.apiHttp.postUrlEncoded(url, {
                _token: '{{ csrf_token() }}',
                status: status,
                questionId: questionId
            }).catch(function(err) {
                $.handleApiFormError(err);
            });
        }
    });

    /* open add agent modal */
    $('body').off('click', "#addQuestion").on('click', '#addQuestion', function() {
        var url = "{{ route('custom-question-settings.create') }}";
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    // add new leave type
    $('body').off('click', ".editQuestion").on('click', '.editQuestion', function() {

        var id = $(this).data('question-id');

        var url = "{{ route('custom-question-settings.edit', ':id') }}";
        url = url.replace(':id', id);

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });
</script>
