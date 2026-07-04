@php
    $deleteUnitTypePermission = user()->permission('manage_finance_setting');
@endphp

<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.unitType.unitType')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
</div>
<div class="modal-body">

    <x-table class="table-bordered" headType="thead-light">
        <x-slot name="thead">
            <th>#</th>
            <th>@lang('modules.unitType.unitType')</th>
            <th class="text-right">@lang('app.action')</th>
        </x-slot>

        @forelse($unitTypes as $key => $unitType)
            <tr id="unit-type-row-{{ $unitType->id }}">
                <td>{{ $key + 1 }}</td>
                <td data-row-id="{{ $unitType->id }}" contenteditable="true">{{ $unitType->unit_type }}</td>
                <td class="text-right">
                    @if ($deleteUnitTypePermission == 'all')
                        <x-forms.button-secondary data-unit-id="{{ $unitType->id }}" icon="trash" class="delete-unit-type">
                            @lang('app.delete')
                        </x-forms.button-secondary>
                    @endif
                </td>
            </tr>
        @empty
            <x-cards.no-record-found-list colspan="3" />
        @endforelse
    </x-table>

    <x-form id="createUnitTypeForm">
        <div class="row border-top-grey ">
            <div class="col-sm-12">
                <x-forms.text fieldId="unit_type" :fieldLabel="__('modules.unitType.unitType')" fieldName="unit_type" fieldRequired="true" :fieldPlaceholder="__('placeholders.category')">
                </x-forms.text>
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-unit-type" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $('.delete-unit-type').click(function() {
        const id = $(this).data('unit-id');
        let url = "{{ route('unit-type.destroy', ':id') }}";
        url = url.replace(':id', id);

        const token = "{{ csrf_token() }}";

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
                    .then((response) => {
                        if (response.status === 'success') {
                            $('#unit-type-row-' + id).fadeOut();
                            if (response.data && typeof window.refreshProductUnitTypeDropdown === 'function') {
                                window.refreshProductUnitTypeDropdown(response.data);
                            }
                        } else if (response.message) {
                            Swal.fire({
                                icon: 'error',
                                text: response.message
                            });
                        }
                    })
                    .catch((error) => {
                        if (typeof $.handleApiFormError === 'function') {
                            $.handleApiFormError(error);
                        }
                    });
            }
        });
    });

    $('#save-unit-type').click(function() {
        const url = "{{ route('unit-type.store') }}";
        $.easyBlockUI('#createUnitTypeForm');

        window.apiHttp.postUrlEncoded(url, $('#createUnitTypeForm').serialize())
            .then((response) => {
                if (response.status === 'success') {
                    if (typeof window.refreshProductUnitTypeDropdown === 'function') {
                        window.refreshProductUnitTypeDropdown(response.data);
                    }
                    $(MODAL_LG).modal('hide');
                }
            })
            .catch((error) => {
                if (typeof $.handleApiFormError === 'function') {
                    $.handleApiFormError(error);
                }
            })
            .finally(() => {
                $.easyUnblockUI('#createUnitTypeForm');
            });
    });

    $('[contenteditable=true]').focus(function() {
        $(this).data("initialText", $(this).html());
    }).blur(function() {
        if ($(this).data("initialText") !== $(this).html()) {
            let id = $(this).data('row-id');
            let value = $(this).html().trim();
            if (!value) return;

            let url = "{{ route('unit-type.update', ':id') }}";
            url = url.replace(':id', id);

            const token = "{{ csrf_token() }}";

            $.easyBlockUI('#unit-type-row-' + id);

            window.apiHttp.postUrlEncoded(url, {
                    'unit_type': value,
                    '_token': token,
                    '_method': 'PUT'
                })
                .then((response) => {
                    if (response.status == 'success' && response.data && typeof window.refreshProductUnitTypeDropdown === 'function') {
                        window.refreshProductUnitTypeDropdown(response.data);
                    }
                })
                .catch((error) => {
                    if (typeof $.handleApiFormError === 'function') {
                        $.handleApiFormError(error);
                    }
                })
                .finally(() => {
                    $.easyUnblockUI('#unit-type-row-' + id);
                });
        }
    });
</script>
