<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<x-form id="edit-save-subtask-data-form" method="PUT">
    <div class="modal-header">
        <h5 class="modal-title" id="modelHeading">@lang('modules.tasks.editSubTask')</h5>
        <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
                aria-hidden="true">×</span></button>
    </div>
    <div class="modal-body">

        <input type="hidden" name="task_id" value="{{ $subTask->task_id }}">
        <div class="row">
            <div class="col-md-12">
                <x-forms.text :fieldLabel="__('app.title')" fieldName="title" fieldRequired="true" fieldId="title"
                    :fieldValue="$subTask->title" :fieldPlaceholder="__('placeholders.task')" />
            </div>

            <div class="col-md-4">
                <x-forms.datepicker fieldId="edit_task_start_date" :fieldLabel="__('app.startDate')" fieldName="start_date"
                    :fieldValue="$subTask->start_date ? $subTask->start_date->format(company()->date_format) : ''"
                    :fieldPlaceholder="__('placeholders.date')" />
            </div>
            <div class="col-md-4">
                <x-forms.datepicker fieldId="edit_task_due_date" :fieldLabel="__('app.dueDate')" fieldName="due_date"
                    :fieldValue="$subTask->due_date ? $subTask->due_date->format(company()->date_format) : ''"
                    :fieldPlaceholder="__('placeholders.date')" />
            </div>
            <div class="col-md-4">
                <div class="form-group my-3">
                    <x-forms.label fieldId="subTaskAssignee"
                        :fieldLabel="__('modules.tasks.assignTo')">
                    </x-forms.label>
                    <x-forms.input-group>
                        <select class="form-control select-picker" name="user_id"
                            id="subTaskAssignee" data-live-search="true">
                            <option value="">--</option>
                            @foreach ($subTask->task->users as $item)
                                <x-user-option
                                    :user="$item"
                                    :selected="($subTask->assigned_to && $subTask->assigned_to == $item->id)">
                                </x-user-option>
                            @endforeach
                        </select>
                    </x-forms.input-group>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group my-3">
                    <x-forms.label fieldId="description" :fieldLabel="__('app.description')">
                    </x-forms.label>
                    <div id="description" style="min-height: 300px;">{!! $subTask->description ?? '' !!}</div>
                    <textarea name="description" id="description-text" class="d-none">{!! $subTask->description ?? '' !!}</textarea>
                </div>
            </div>
            <div class="col-md-12">
                <a class="f-15 f-w-500" href="javascript:;" id="add-sub-task-file"><i
                        class="fa fa-paperclip font-weight-bold mr-1"></i>@lang('modules.projects.uploadFile')</a>
            </div>
            <div class="col-lg-12 add-file-box-edit d-none">
                <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2"
                :fieldLabel="__('modules.projects.uploadFile')" fieldName="file"
                fieldId="sub-task-file-upload-dropzone" />
                <input type="hidden" name="image_url" id="image_url">
            </div>
            <div class="col-md-12 add-file-box-edit d-none mb-5">
                <div class="w-100 justify-content-end d-flex mt-2">
                    <x-forms.button-cancel id="cancel-sub-task-file" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </div>
            </div>
            <input type="hidden" name="subTaskID" id="subTaskID">
            <input type="hidden" name="addedFiles" id="addedFiles">
        </div>

    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
        <x-forms.button-primary id="edit-save-subtask" icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</x-form>

<script>
    $(document).ready(function() {

        quillImageLoad('#description');

        $('.select-picker').selectpicker();

        $('#add-sub-task-file').click(function() {
            $('.add-file-box-edit').removeClass('d-none');
            $('#add-sub-task-file').addClass('d-none');
        });

        $('#cancel-sub-task-file').click(function() {
            $('.add-file-box-edit').addClass('d-none');
            $('#add-sub-task-file').removeClass('d-none');
            return false;
        });

        datepicker('#edit_task_start_date', {
            position: 'bl',
            @if ($subTask->start_date)
            dateSelected: new Date("{{ str_replace('-', '/', $subTask->start_date) }}"),
            @endif
            ...datepickerConfig
        });

        datepicker('#edit_task_due_date', {
            position: 'bl',
            @if ($subTask->due_date)
            dateSelected: new Date("{{ str_replace('-', '/', $subTask->due_date) }}"),
            @endif
            ...datepickerConfig
        });

        Dropzone.autoDiscover = false;
        //Dropzone class
        subTaskDropzone = new Dropzone("div#sub-task-file-upload-dropzone", {
            dictDefaultMessage: "{{ __('app.dragDrop') }}",
            url: "{{ route('sub-task-files.store') }}",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            paramName: "file",
            maxFilesize: DROPZONE_MAX_FILESIZE,
            maxFiles: DROPZONE_MAX_FILES,
            autoProcessQueue: false,
            uploadMultiple: true,
            addRemoveLinks: true,
            parallelUploads: DROPZONE_MAX_FILES,
            acceptedFiles: DROPZONE_FILE_ALLOW,
            init: function() {
                subTaskDropzone = this;
            }
        });
        subTaskDropzone.on('sending', function(file, xhr, formData) {
            var ids = "{{ $subTask->id }}";
            formData.append('sub_task_id', ids);
            $.easyBlockUI();
        });
        subTaskDropzone.on('uploadprogress', function() {
            $.easyBlockUI();
        });
        subTaskDropzone.on('queuecomplete', function(resp) {
            window.location.reload();
        });
        subTaskDropzone.on('removedfile', function () {
            var grp = $('div#file-upload-dropzone').closest(".form-group");
            var label = $('div#file-upload-box').siblings("label");
            $(grp).removeClass("has-error");
            $(label).removeClass("is-invalid");
        });
        subTaskDropzone.on('error', function (file, message) {
            subTaskDropzone.removeFile(file);
            var grp = $('div#file-upload-dropzone').closest(".form-group");
            var label = $('div#file-upload-box').siblings("label");
            $(grp).find(".help-block").remove();
            var helpBlockContainer = $(grp);

            if (helpBlockContainer.length == 0) {
                helpBlockContainer = $(grp);
            }

            helpBlockContainer.append('<div class="help-block invalid-feedback">' + message + '</div>');
            $(grp).addClass("has-error");
            $(label).addClass("is-invalid");

        });

        $('#edit-save-subtask').click(function() {

            document.getElementById('description-text').value = document.getElementById('description').children[0].innerHTML;

            const url = "{{ route('sub-tasks.update', $subTask->id) }}";

            var $saveBtn = $('#edit-save-subtask-data-form').find('#edit-save-subtask');
            var savePrev = $saveBtn.html();
            $saveBtn.attr('data-prev-text', savePrev);
            $saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + (document.loading || 'Loading...'));
            $.easyBlockUI('#edit-save-subtask-data-form');
            window.apiHttp.postUrlEncoded(url, $('#edit-save-subtask-data-form').serialize()).then(function(response) {
                if (response.status == "success") {
                    if (typeof response.message !== 'undefined' && response.message) {
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
                    if (subTaskDropzone.getQueuedFiles().length > 0) {
                        subTaskDropzone.processQueue();
                   } else {
                        $('#sub-task-list').html(response.view);
                        $(MODAL_LG).modal('hide');
                    }
                }
            }).catch(function(err) {
                $.handleApiFormError(err);
            }).finally(function() {
                $.easyUnblockUI('#edit-save-subtask-data-form');
                $saveBtn.html($saveBtn.attr('data-prev-text'));
                $saveBtn.prop('disabled', false);
            });
        });

    });

</script>
