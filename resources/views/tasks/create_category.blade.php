@php
    $deleteTaskCategoryPermission = user()->permission('delete_task_category');
@endphp

<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.tasks.taskCategory')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-table class="table-bordered" headType="thead-light">
        <x-slot name="thead">
            <th>#</th>
            <th>@lang('modules.projectCategory.categoryName')</th>
            <th class="text-right">@lang('app.action')</th>
        </x-slot>

        @forelse($categories as $key=>$category)
            <tr id="cat-{{ $category->id }}">
                <td>{{ $key + 1 }}</td>
                <td data-row-id="{{ $category->id }}" contenteditable="true">{{ $category->category_name }}</td>
                <td class="text-right">
                    @if ($deleteTaskCategoryPermission == 'all' || $deleteTaskCategoryPermission == 'added')
                        <x-forms.button-secondary data-cat-id="{{ $category->id }}" icon="trash" class="delete-category">
                            @lang('app.delete')
                        </x-forms.button-secondary>
                    @endif
                </td>
            </tr>
        @empty
            <x-cards.no-record-found-list />
        @endforelse
    </x-table>

    <x-form id="createTaskCategory">
        <div class="row border-top-grey ">
            <div class="col-sm-12">
                <x-forms.text fieldId="category_name" :fieldLabel="__('modules.projectCategory.categoryName')"
                    fieldName="category_name" fieldRequired="true" :fieldPlaceholder="__('placeholders.category')">
                </x-forms.text>
            </div>

        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-category" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $('.delete-category').click(function() {

        var id = $(this).data('cat-id');
        var url = "{{ route('taskCategory.destroy', ':id') }}";
        url = url.replace(':id', id);

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
                window.apiHttp.delete(url, "{{ csrf_token() }}").then(function(response) {
                    if (response.status == 'success') {
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
                        $('#task_category_id').html(response.data);
                        $('#task_category_id').selectpicker('refresh');
                        $(MODAL_LG).modal('hide');
                    }
                }).catch(function(err) {
                    $.handleApiFormError(err);
                });
            }
        });

    });

    $('#save-category').click(function() {
        var url = "{{ route('taskCategory.store') }}";
        var $btn = $('#createTaskCategory').find('#save-category');
        var btnPrev = $btn.html();
        $btn.attr('data-prev-text', btnPrev);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + (document.loading || 'Loading...'));
        $.easyBlockUI('#createTaskCategory');
        window.apiHttp.postUrlEncoded(url, $('#createTaskCategory').serialize()).then(function(response) {
            if (response.status == 'success') {
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
                $('#task_category_id').html(response.data);
                $('#task_category_id').selectpicker('refresh');
                $(MODAL_LG).modal('hide');
            }
        }).catch(function(err) {
            $.handleApiFormError(err);
        }).finally(function() {
            $.easyUnblockUI('#createTaskCategory');
            $btn.html($btn.attr('data-prev-text'));
            $btn.prop('disabled', false);
        });
    });

    $('[contenteditable=true]').focus(function() {
        $(this).data("initialText", $(this).html());
        let rowId = $(this).data('row-id');
    }).blur(function() {
        if ($(this).data("initialText") !== $(this).html()) {
            let id = $(this).data('row-id');
            let value = $(this).html();

            if (id == null || id == '') {
                return false;
            }
            
            var url = "{{ route('taskCategory.update', ':id') }}";
            url = url.replace(':id', id);

            $.easyBlockUI('#row-' + id);
            window.apiHttp.postUrlEncoded(url, {
                'category_name': value,
                '_token': "{{ csrf_token() }}",
                '_method': 'PUT'
            }).then(function(response) {
                if (response.status == 'success') {
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
                    $('#task_category_id').html(response.data);
                    $('#task_category_id').selectpicker('refresh');
                }
            }).catch(function(err) {
                $.handleApiFormError(err);
            }).finally(function() {
                $.easyUnblockUI('#row-' + id);
            });
        }
    });

</script>
