@php
    $deleteProjectCategoryPermission = user()->permission('manage_project_category');
@endphp

<style>
    #myModalDefault {
        z-index: 1060;
    }
</style>

<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('purchase::modules.inventory.adjustmentReason')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-table class="table-bordered" headType="thead-light">
        <x-slot name="thead">
            <th>#</th>
            <th>@lang('purchase::modules.inventory.reasonName')</th>
            <th class="text-right">@lang('app.action')</th>
        </x-slot>

        @forelse($reasons as $key=>$reason)
            <tr id="reason-{{ $reason->id }}">
                <td>{{ $key + 1 }}</td>
                <td data-row-id="{{ $reason->id }}" contenteditable="true">{{ mb_ucwords($reason->name) }}</td>
                <td class="text-right">
                    @if ($deleteProjectCategoryPermission == 'all' || ($deleteProjectCategoryPermission == 'added' && $category->added_by == user()->id))
                        <x-forms.button-secondary data-reason-id="{{ $reason->id }}" icon="trash" class="delete-reason">
                            @lang('app.delete')
                        </x-forms.button-secondary>
                    @endif
            </tr>
        @empty
            <x-cards.no-record-found-list />
        @endforelse
    </x-table>

    <x-form id="createReasonName">
        <div class="row border-top-grey ">
            <div class="col-sm-12">
                <x-forms.text fieldId="reason_name" :fieldLabel="__('purchase::modules.inventory.reasonName')" fieldName="reason_name" fieldRequired="true" :fieldPlaceholder="__('purchase::placeholders.reasonName')">
                </x-forms.text>
            </div>

        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3" id="close-modal">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-reason" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $('#close-modal').click(function() {
        $('#myModal').css('overflow', 'auto');
    });

    $('.delete-reason').click(function() {

        var id = $(this).data('reason-id');
        var url = "{{ route('adjustment-reasons.destroy', ':id') }}";
        url = url.replace(':id', id);

        var token = "{{ csrf_token() }}";

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
                window.apiHttp.delete(url, token)
                    .then(function(response) {
                        if (response.status == 'success') {
                            $('#reason-' + id).fadeOut();
                            $('#adjustment_reason_id').html(response.data);
                            $('#adjustment_reason_id').selectpicker('refresh');
                        }
                    })
                    .catch(function(err) {
                        $.handleApiFormError(err);
                    });
            }
        });

    });

    $('#save-reason').click(function() {
        var url = "{{ route('adjustment-reasons.store') }}";
        window.apiHttp.postUrlEncoded(url, $('#createReasonName').serialize())
            .then(function(response) {
                if (response.status == 'success') {
                    $('#adjustment_reason_id').html(response.data);
                    $('#adjustment_reason_id').selectpicker('refresh');
                    $(MODAL_DEFAULT).modal('hide');
                    $('#myModal').css('overflow', 'auto');
                }
            })
            .catch(function(err) {
                $.handleApiFormError(err);
            });
    });

    $('[contenteditable=true]').focus(function() {
        $(this).data("initialText", $(this).html());
        let rowId = $(this).data('row-id');
    }).blur(function() {
        if ($(this).data("initialText") !== $(this).html()) {
            let id = $(this).data('row-id');
            let value = $(this).html();

            var url = "{{ route('adjustment-reasons.update', ':id') }}";
            url = url.replace(':id', id);

            var token = "{{ csrf_token() }}";

            window.apiHttp.postUrlEncoded(url, {
                    reason_name: value,
                    _token: token,
                    _method: 'PUT'
                })
                .then(function(response) {
                    if (response.status == 'success') {
                        $('#adjustment_reason_id').html(response.data);
                        $('#adjustment_reason_id').selectpicker('refresh');
                    }
                })
                .catch(function(err) {
                    $.handleApiFormError(err);
                });
        }
    });
</script>
