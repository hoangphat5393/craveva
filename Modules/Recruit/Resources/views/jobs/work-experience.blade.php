<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('recruit::app.job.workexperience')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
</div>
<div class="modal-body" id="work-experience-table">
    <x-table class="table-bordered" headType="thead-light">
        <x-slot name="thead">
            <th>#</th>
            <th class="w-75">@lang('recruit::app.job.workexperience')</th>
            <th class="text-right">@lang('app.action')</th>
        </x-slot>

        @forelse($workExperience as $key=>$item)
            <tr id="row-{{ $item->id }}">
                <td>{{ $key + 1 }}</td>
                <td data-row-id="{{ $item->id }}" contenteditable="true">{{ $item->work_experience }}</td>
                <td class="text-right">

                    <x-forms.button-secondary data-row-id="{{ $item->id }}" icon="trash" class="delete-row">
                        @lang('app.delete')</x-forms.button-secondary>
            </tr>
        @empty
            <x-cards.no-record-found-list />
        @endforelse
    </x-table>

    <x-form id="createProjectCategory">
        <div class="row border-top-grey ">
            <div class="col-sm-12">
                <x-forms.text fieldId="work_experience" :fieldLabel="__('app.name')" fieldName="work_experience" fieldRequired="true" fieldPlaceholder="e.g: Fresher">
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
    $('body').on('click', '.delete-row', function() {
        var id = $(this).data('row-id');
        var url = "{{ route('work-experience.destroy', ':id') }}";
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
                window.apiHttp.delete(url, {
                    _token: "{{ csrf_token() }}"
                }).then(function(response) {
                    if (response.data.status == "success") {
                        $('#row-' + id).fadeOut();
                        $('#work_experience_1').html(response.data.data);
                        $('#work_experience_1').selectpicker('refresh');
                    }
                }).catch(function(err) {
                    $.handleApiFormError(err);
                });
            }
        });

    });

    $('body').off('click', "#save-category").on('click', '#save-category', function() {
        var url = "{{ route('work-experience.store') }}";
        window.apiHttp.postUrlEncoded(url, $('#createProjectCategory').serialize())
            .then(function(response) {
                if (response.data.status == 'success') {
                    $('#work_experience_1').html(response.data.data);
                    $('#work_experience_1').selectpicker('refresh');
                    $(MODAL_LG).modal('hide');
                }
            })
            .catch(function(err) {
                $.handleApiFormError(err);
            });
    });


    $('#work-experience-table [contenteditable=true]').focus(function() {
        $(this).data("initialText", $(this).html());
        let rowId = $(this).data('row-id');
    }).blur(function() {
        // ...if content is different...
        if ($(this).data("initialText") !== $(this).html()) {
            let id = $(this).data('row-id');
            let value = $(this).html();

            var url = "{{ route('work-experience.update', ':id') }}";
            url = url.replace(':id', id);

            window.apiHttp.postUrlEncoded(url, {
                work_experience: value,
                _token: "{{ csrf_token() }}",
                _method: 'PUT'
            }).then(function(response) {
                if (response.data.status == 'success') {
                    $('#work_experience_1').html(response.data.data);
                    $('#work_experience_1').selectpicker('refresh');
                }
            }).catch(function(err) {
                $.handleApiFormError(err);
            });
        }
    });
</script>
