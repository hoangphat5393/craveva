<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.tasks.comment')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">

    <x-form id="edit-comment-data-form" method="PUT">
        <div class="row">
            <div class="col-md-12 p-20 ">
                <div class="media">
                    <img src="{{ $comment->user->image_url }}" class="align-self-start mr-3 taskEmployeeImg rounded"
                        alt="{{ $comment->user->name }}">
                    <div class="media-body bg-white">
                        <div class="form-group">
                            <div id="task-edit-comment">{!! $comment->comment !!}</div>
                            <textarea name="comment" class="form-control invisible d-none"
                                id="task-edit-comment-text">{!!  $comment->comment !!}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-edit-comment" icon="check">@lang('app.save')</x-forms.button-primary>
</div>


<script>
    var edit_task_comments = "{{ user()->permission('edit_task_comments') }}";

    $(document).ready(function() {
            const atValues = @json($taskuserData);

            if (add_task_comments == "all" || add_task_comments == "added") {
                quillMention(atValues, '#task-edit-comment');
            }

            $('#save-edit-comment').click(function() {
            var comment = document.getElementById('task-edit-comment').children[0].innerHTML;
            document.getElementById('task-edit-comment-text').value = comment;
            var mention_user_id = $('#task-edit-comment span[data-id]').map(function(){
                return $(this).attr('data-id')
            }).get();

            const url = "{{ route('taskComment.update', $comment->id) }}";

            var $saveBtn = $('#edit-comment-data-form').find('#save-edit-comment');
            var savePrev = $saveBtn.html();
            $saveBtn.attr('data-prev-text', savePrev);
            $saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + (document.loading || 'Loading...'));
            $.easyBlockUI('#edit-comment-data-form');
            window.apiHttp.postUrlEncoded(url, $.param({
                '_token': '{{ csrf_token() }}',
                comment: comment,
                mention_user_id: mention_user_id,
                '_method': 'PUT',
                taskId: '{{ $comment->task->id }}'
            })).then(function(response) {
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
                    destory_editor('#task-edit-comment');

                    document.getElementById('comment-list').innerHTML = response.view;
                    $(MODAL_XL).modal('hide');

                }
            }).catch(function(err) {
                $.handleApiFormError(err);
            }).finally(function() {
                $.easyUnblockUI('#edit-comment-data-form');
                $saveBtn.html($saveBtn.attr('data-prev-text'));
                $saveBtn.prop('disabled', false);
            });
        });

    });

</script>
<style>
    #task-edit-comment .ql-editor {
        min-height: 250px;
    }
</style>
