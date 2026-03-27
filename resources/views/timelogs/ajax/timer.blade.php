@php
    $addTaskPermission = user()->permission('add_tasks');
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.timeLogs.startTimer')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="startTimerForm">
        <input type="hidden" name="user_id[]" value="{{ user()->id }}">
        <div class="row">
            <div class="col">
                <x-forms.select fieldId="project_id" fieldName="project_id" :fieldLabel="__('app.project')" search="true">
                    <option value="">--</option>
                    @foreach ($projects as $data)
                        <option value="{{ $data->id }}">
                            {{ $data->project_name }}
                        </option>
                    @endforeach
                </x-forms.select>
            </div>
        </div>
        <div class="row">
            <div class="col" id="task_div">
                <x-task-selection-dropdown :tasks="$tasks" />
            </div>
        </div>

        <div class="row">
            @if ($addTaskPermission == 'all' || $addTaskPermission == 'added')
                <div class="col">
                    <div class="form-group">
                        <div class="d-flex mt-3">
                            <x-forms.checkbox :fieldLabel="__('app.create') . ' ' . __('modules.tasks.newTask')" fieldName="create_task" fieldId="create_task" />
                        </div>
                    </div>
                </div>
            @endif

            <div class="col-12">
                <x-forms.text fieldId="memo" fieldName="memo" :fieldLabel="__('modules.timeLogs.memo')" fieldRequired="true" />
            </div>
        </div>

    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="start-timer-btn" icon="play">@lang('modules.timeLogs.startTimer')</x-forms.button-primary>
</div>

<script>
    $('#start-timer-btn').click(function() {
        var url = "{{ route('timelogs.start_timer') }}";
        var $btn = $('#start-timer-btn');
        $btn.prop('disabled', true);
        $.easyBlockUI('#startTimerForm');
        window.apiHttp.postUrlEncoded(url, $('#startTimerForm').serialize()).then(function(response) {
            if (response.status == 'success') {

                if (response.activeTimerCount > 0) {
                    $('#show-active-timer .active-timer-count').html(response.activeTimerCount);
                    $('#show-active-timer .active-timer-count').removeClass('d-none');
                } else {
                    $('#show-active-timer .active-timer-count').addClass('d-none');
                }

                $('#timer-clock').html(response.clockHtml);

                $(MODAL_XL).modal('hide');
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
            $btn.prop('disabled', false);
            $.easyUnblockUI('#startTimerForm');
        });
    });

    $("input[name=create_task]").click(function() {
        $('#task_div').toggleClass('d-none');
    });

    $('#startTimerForm').on('change', '#project_id', function() {
        let id = $(this).val();
        if (id === '') {
            id = 0;
        }
        let url = "{{ route('projects.pendingTasks', ':id') }}";
        url = url.replace(':id', id);

        $.easyBlockUI('#startTimerForm');
        window.apiHttp.get(url).then(function(response) {
            if (response.status == 'success') {
                $('#timer_task_id').html(response.data);
                $('#timer_task_id').selectpicker('refresh');
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
            $.easyUnblockUI('#startTimerForm');
        });
    });

    init(MODAL_XL);
</script>
