<style>
    .suggest-colors a {
        border-radius: 4px;
        width: 30px;
        height: 30px;
        display: inline-block;
        margin-right: 10px;
        margin-bottom: 10px;
        text-decoration: none;
    }

</style>
<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('app.menu.projectLabel')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-table class="table-bordered" headType="thead-light">
        <x-slot name="thead">
            <th>#</th>
            <th>@lang('app.labelName')</th>
            <th>@lang('modules.sticky.colors')</th>
            <th>@lang('app.description')</th>
            <th class="text-right">@lang('app.action')</th>
        </x-slot>

        @forelse($projectLabels as $key=>$item)
            <tr id="label-{{ $item->id }}">
                <td>{{ $key + 1 }}</td>
                <td data-row-id="{{ $item->id }}" data-column="label_name" contenteditable="true">
                    {!! $item->label_name !!}
                </td>
                <td data-row-id="{{ $item->id }}" data-column="label_color" contenteditable="true">
                    {!! $item->label_color !!}
                </td>
                <td data-row-id="{{ $item->id }}" data-column="description" contenteditable="true">{!! $item->description !!}
                </td>
                <td class="text-right">
                    {{-- @if (user()->permission('project_labels') == 'all') --}}
                        <x-forms.button-secondary data-label-id="{{ $item->id }}" icon="trash" class="delete-label">
                            @lang('app.delete')
                        </x-forms.button-secondary>
                    {{-- @endif --}}
                </td>
            </tr>
        @empty
            <x-cards.no-record-found-list colspan="5" />
        @endforelse
    </x-table>

    <x-form id="createProjectLabelForm">
        <div class="row border-top-grey ">
            <div class="col-md-6">
                <x-forms.text fieldId="label_name" :fieldLabel="__('app.label') .' '. __('app.name')"
                    fieldName="label_name" fieldRequired="true" :fieldPlaceholder="__('placeholders.label')">
                </x-forms.text>
            </div>
            <div class="col-md-6">
                <x-forms.text fieldId="label_color" :fieldLabel="__('modules.sticky.colors')" fieldName="color"
                    fieldRequired="true">
                </x-forms.text>
            </div>
            <div class="col-sm-12 col-md-12">
                <x-forms.textarea :fieldLabel="__('app.description')" fieldName="description" fieldId="description">
                </x-forms.textarea>
            </div>
            <div class="col-md-12">
                <div class="suggest-colors">
                    <a style="background-color: #0033CC" data-color="#0033CC" href="javascript:;">&nbsp;
                    </a><a style="background-color: #428BCA" data-color="#428BCA" href="javascript:;">&nbsp;
                    </a><a style="background-color: #CC0033" data-color="#CC0033" href="javascript:;">&nbsp;
                    </a><a style="background-color: #44AD8E" data-color="#44AD8E" href="javascript:;">&nbsp;
                    </a><a style="background-color: #A8D695" data-color="#A8D695" href="javascript:;">&nbsp;
                    </a><a style="background-color: #5CB85C" data-color="#5CB85C" href="javascript:;">&nbsp;
                    </a><a style="background-color: #69D100" data-color="#69D100" href="javascript:;">&nbsp;
                    </a><a style="background-color: #004E00" data-color="#004E00" href="javascript:;">&nbsp;
                    </a><a style="background-color: #34495E" data-color="#34495E" href="javascript:;">&nbsp;
                    </a><a style="background-color: #7F8C8D" data-color="#7F8C8D" href="javascript:;">&nbsp;
                    </a><a style="background-color: #A295D6" data-color="#A295D6" href="javascript:;">&nbsp;
                    </a><a style="background-color: #5843AD" data-color="#5843AD" href="javascript:;">&nbsp;
                    </a><a style="background-color: #8E44AD" data-color="#8E44AD" href="javascript:;">&nbsp;
                    </a><a style="background-color: #FFECDB" data-color="#FFECDB" href="javascript:;">&nbsp;
                    </a><a style="background-color: #AD4363" data-color="#AD4363" href="javascript:;">&nbsp;
                    </a><a style="background-color: #D10069" data-color="#D10069" href="javascript:;">&nbsp;
                    </a><a style="background-color: #FF0000" data-color="#FF0000" href="javascript:;">&nbsp;
                    </a><a style="background-color: #D9534F" data-color="#D9534F" href="javascript:;">&nbsp;
                    </a><a style="background-color: #D1D100" data-color="#D1D100" href="javascript:;">&nbsp;
                    </a><a style="background-color: #F0AD4E" data-color="#F0AD4E" href="javascript:;">&nbsp;
                    </a><a style="background-color: #AD8D43" data-color="#AD8D43" href="javascript:;">&nbsp;
                    </a>
                </div>
            </div>
            <input type="hidden" name="project_id" id="project_id" value="{{$projectId}}">
            <input type="hidden" name="project_template_project_id" id="project_template_project_id" value="{{$projectTemplateProjectId}}">
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-label" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $('.suggest-colors a').click(function() {
        var color = $(this).data('color');
        $('#label_color').val(color);
        $('.asColorPicker-trigger span').css('background', color);
    });

    $(".select-picker").selectpicker();


    $('.delete-label').click(function() {
        var projectId = $('#project_id').val();
        var projectTemplateProjectId = $('#project_template_project_id').val();
        var id = $(this).data('label-id');
        var url = "{{ route('project-label.destroy', ':id') }}";
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
                window.apiHttp.postUrlEncoded(url, {
                    projectId: projectId,
                    projectTemplateProjectId: projectTemplateProjectId,
                    _token: "{{ csrf_token() }}",
                    _method: 'DELETE'
                }).then(function(response) {
                    if (response.status == 'success') {
                        $('#label-'+id).remove();
                        $('#project_labels').html(response.data);
                        $('#project_labels').selectpicker('refresh');
                    }
                }).catch(function(err) {
                    $.handleApiFormError(err);
                });
            }
        });

    });

    $('#save-label').click(function() {
        var url = "{{ route('project-label.store') }}";
        window.apiHttp.postUrlEncoded(url, $('#createProjectLabelForm').serialize()).then(function(response) {
            if (response.status == 'success') {
                $('#project_labels').html(response.data);
                $(MODAL_XL).modal('hide');
                $('#project_labels').selectpicker('refresh');
            }
        }).catch(function(err) {
            $.handleApiFormError(err);
        });
    });

    $('[contenteditable=true]').focus(function() {
        $(this).data("initialText", $(this).html());
        let rowId = $(this).data('row-id');
    }).blur(function() {
        if ($(this).data("initialText") !== $(this).html()) {
            let id = $(this).data('row-id');
            let tableId = $(this).parent().attr('id');

            if(id){
                var url = "{{ route('project-label.update', ':id') }}";
                url = url.replace(':id', id);
                var projectId = "{{ $projectId }}";
                let selectedLabels = $('#project_labels').val();

                $('#'+tableId).each(function() {
                    let labelName =  $(this).find("td:nth-child(2)").html();
                    let labelColor =  $(this).find("td:nth-child(3)").html();
                    let description =  $(this).find("td:nth-child(4)").html();

                    $.easyBlockUI('#row-' + id);
                    window.apiHttp.postUrlEncoded(url, {
                        label_name: labelName,
                        color: labelColor,
                        description: description,
                        parent_project_id: projectId,
                        _token: "{{ csrf_token() }}",
                        _method: 'PUT'
                    }).then(function(response) {
                        if (response.status == 'success') {
                            $('#project_labels').selectpicker('refresh');
                            $('#project_labels').html(response.data);
                            $('#project_labels').val(selectedLabels);
                            $('#project_labels').selectpicker('refresh');

                        }
                    }).catch(function(err) {
                        $.handleApiFormError(err);
                    }).finally(function() {
                        $.easyUnblockUI('#row-' + id);
                    });

                });
            }

        }
    });

    $.fn.projectLabel = function(projectId, selectedLabels){
        let id = projectId;

        if (id === '') {
            id = 0;
        }
        let url = "{{ route('project-label.edit', ':id') }}";
        url = url.replace(':id', id);
        $.easyBlockUI('#save-project-data-form');
        window.apiHttp.get(url).then(function (data) {
            $('#project_labels').html(data.data);
            $('#project_labels').val(selectedLabels);
            $('#project_labels').selectpicker('refresh');
        }).catch(function(err) {
            $.handleApiFormError(err);
        }).finally(function() {
            $.easyUnblockUI('#save-project-data-form');
        });
    }

</script>
