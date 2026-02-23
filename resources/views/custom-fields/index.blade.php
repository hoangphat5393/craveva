@extends('layouts.app')

@push('styles')
    <style>
        .custom-fields-table table tbody tr {
            cursor: move;
        }
    </style>
@endpush

@section('content')
    <div class="w-100 d-flex">
        @include('sections.setting-sidebar')

        <x-setting-card>

            <x-slot name="buttons">
                <div class="row">
                    <div class="col-md-12 mb-2">
                        <x-forms.button-primary icon="plus" id="add-field" class="mb-2"> @lang('modules.customFields.addField')
                        </x-forms.button-primary>
                    </div>
                </div>
            </x-slot>

            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <h2 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                        @lang($pageTitle)
                    </h2>
                </div>
            </x-slot>

            <div class="table-responsive p-20 pipelineData">
                <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100">
                    @forelse($groupedCustomFields as $module => $fields)
                        <div class="row no-gutters border rounded my-3 px-4 py-2" id="removeModule{{ $module }}">
                            <div class="col-md-6">
                                <div class="heading-h4">
                                    {{ $module }}
                                </div>

                                <div class="simple-text text-lightest mt-1">
                                    <span id="moduleCount{{ $module }}">{{ $fields->count() }}</span>

                                    @if ($fields->count() == 1)
                                        @lang('modules.customFields.field')
                                    @else
                                        @lang('modules.customFields.fields')
                                    @endif
                                </div>

                            </div>
                            <div class="col-md-2 text-right module-header" data-module="{{ $module }}" style="margin-left: 390px;">
                                <x-forms.button-secondary class="view-pipeline">
                                    <i class="side-icon bi bi-kanban"></i>
                                    @lang('modules.customFields.viewFields')
                                </x-forms.button-secondary>
                            </div>
                        </div>

                        <div class="custom-fields-table" data-module="{{ $module }}" style="display: none;">
                            <x-table class="table-bordered" id="removeModuleColumns{{ $module }}">
                                <x-slot name="thead">
                                    <th>@lang('modules.customFields.moduleLabel')</th>
                                    <th>@lang('modules.customFields.type')</th>
                                    <th>@lang('modules.customFields.values')</th>
                                    <th>@lang('modules.customFields.required')</th>
                                    <th>@lang('modules.customFields.showInTable')</th>
                                    <th>@lang('modules.customFields.export')</th>
                                    <th>@lang('app.action')</th>
                                </x-slot>
                                @forelse($fields as $field)
                                    <tr class="row{{ $field->id }}" data-row-id="{{ $field->id }}">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="editable-label cursor-pointer" data-id="{{ $field->id }}">{{ __($field->label) }} <i class="fa fa-pencil ml-2 text-lightest"></i></span>
                                                <input type="text" class="form-control height-35 f-14 edit-label-input" value="{{ $field->label }}" data-id="{{ $field->id }}" style="display: none;">
                                            </div>
                                        </td>
                                        <td>{{ $field->type }}</td>
                                        <td>
                                            @php
                                                $fieldValues = json_decode($field->values);
                                            @endphp
                                            @if (!empty($fieldValues) && is_array($fieldValues))
                                                <ul class="value-list">
                                                    @foreach ($fieldValues as $value)
                                                        <li>{{ $value }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                --
                                            @endif
                                        </td>
                                        <td>
                                            @if ($field->required === 'yes')
                                                <span class="badge badge-danger disabled color-palette">@lang('app.yes')</span>
                                            @else
                                                <span class="badge badge-secondary disabled color-palette">@lang('app.no')</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($field->visible == 'true')
                                                <span class="badge badge-danger disabled color-palette">@lang('app.yes')</span>
                                            @else
                                                <span class="badge badge-secondary disabled color-palette">@lang('app.no')</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($field->export == 1)
                                                <span class="badge badge-danger disabled color-palette">@lang('app.yes')</span>
                                            @else
                                                <span class="badge badge-secondary disabled color-palette">@lang('app.no')</span>
                                            @endif
                                        </td>

                                        <td>
                                            <div class="task_view">
                                                <a data-user-id="{{ $field->id }}" class="task_view_more d-flex align-items-center justify-content-center edit-custom-field" href="javascript:;">
                                                    <i class="fa fa-edit icons mr-2"></i> {{ __('app.edit') }}
                                                </a>
                                            </div>
                                            <div class="task_view">
                                                <a data-user-id="{{ $field->id }}" data-module="{{ $module }}" class="task_view_more d-flex align-items-center justify-content-center sa-params" href="javascript:;">
                                                    <i class="fa fa-trash icons mr-2"></i> {{ __('app.delete') }}
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">
                                            <x-cards.no-record icon="list" :message="__('messages.noCustomField')" />
                                        </td>
                                    </tr>
                                @endforelse
                            </x-table>
                        </div>
                    @empty
                        <div class="align-items-center d-flex flex-column text-lightest p-20 w-100">
                            <i class="fa fa-clipboard f-21 w-100"></i>

                            <div class="f-15 mt-4">
                                - @lang('messages.noRecordFound') -
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

        </x-setting-card>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        $(function() {

            $('.custom-fields-table table tbody').each(function() {
                var el = this;
                Sortable.create(el, {
                    animation: 150,
                    onEnd: function(evt) {
                        var order = [];
                        $(el).find('tr').each(function(index, element) {
                            order.push($(element).data('row-id'));
                        });

                        var url = "{{ route('custom-fields.order') }}";
                        var token = "{{ csrf_token() }}";

                        $.easyAjax({
                            type: 'POST',
                            url: url,
                            data: {
                                '_token': token,
                                'order': order
                            }
                        });
                    }
                });
            });

            // Hide all custom field tables initially
            $('.custom-fields-table').hide();

            // Toggle visibility of the custom fields table on module header click
            $('.module-header').click(function() {
                var module = $(this).data('module');
                var table = $('.custom-fields-table[data-module="' + module + '"]');
                table.toggle();
            });

            $('body').on('click', '.sa-params', function() {
                const id = $(this).data('user-id');
                var module = $(this).data('module');

                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')",
                    text: "@lang('messages.deleteField')",
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

                        let url = "{{ route('custom-fields.destroy', ':id') }}";
                        url = url.replace(':id', id);

                        const token = "{{ csrf_token() }}";

                        $.easyAjax({
                            type: 'POST',
                            url: url,
                            blockUI: true,
                            data: {
                                '_token': token,
                                '_method': 'DELETE'
                            },
                            success: function(response) {
                                if (response.status == "success") {
                                    $('.row' + id).fadeOut();
                                    const updatedCount = response.updatedCount;
                                    $('#moduleCount' + module).html(updatedCount);
                                    if (updatedCount == 0) {
                                        $('#removeModule' + module).fadeOut().remove();
                                        $('#removeModuleColumns' + module).fadeOut().remove();
                                    }
                                }
                            }
                        });
                    }
                });
            });

        });

        function updateFieldCount(module) {
            let fieldCount = $('.custom-fields-table[data-module="' + module + '"] tr').length - 1;
            let fieldText = fieldCount === 1 ? '@lang('modules.customFields.field')' : '@lang('modules.customFields.fields')';
            console.log(fieldCount + ' ,' + fieldText);
            $('.module-header[data-module="' + module + '"]').siblings('.heading-h4').find('.simple-text').text(fieldCount + ' ' + fieldText);
        }

        $('body').on('click', '#add-field', function() {
            const url = "{{ route('custom-fields.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('body').on('click', '.edit-custom-field', function() {
            const id = $(this).data('user-id');
            let url = "{{ route('custom-fields.edit', ':id') }}";
            url = url.replace(':id', id);
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        // Inline editing for Label
        $('body').on('click', '.editable-label', function() {
            var id = $(this).data('id');
            var input = $(this).siblings('.edit-label-input');
            
            $(this).hide();
            input.show().focus();
        });

        $('body').on('blur', '.edit-label-input', function() {
            var id = $(this).data('id');
            var value = $(this).val();
            var input = $(this);
            var span = $(this).siblings('.editable-label');
            var originalValue = span.text().trim(); // Note: span text might include the icon text if any, but icon is <i> so .text() gets it. 
            // Wait, my span has <i class="fa fa-pencil"></i> inside. .text() will get the label text only if I am careful.
            // Actually, the span content is "{{ __($field->label) }} <i ...></i>".
            // .text() will ignore the html tag but return the text.
            // But when I set span.text(value), I will lose the icon.
            // I should reconstruct the html.
            
            // Let's check logic below.

            if (value === '') {
                 input.hide();
                 span.show();
                 return;
            }

            var url = "{{ route('custom-fields.ajax_update_label', ':id') }}";
            url = url.replace(':id', id);
            var token = "{{ csrf_token() }}";

            $.easyAjax({
                url: url,
                container: '.row' + id,
                type: "POST",
                data: {
                    '_token': token,
                    'label': value
                },
                success: function(response) {
                    if (response.status == 'success') {
                        span.html(value + ' <i class="fa fa-pencil ml-2 text-lightest"></i>');
                    } else {
                        input.val(originalValue); // This might be wrong if originalValue had extra chars
                    }
                    input.hide();
                    span.show();
                },
                error: function(response) {
                     input.hide();
                     span.show();
                }
            })
        });

        // Handle Enter key
        $('body').on('keypress', '.edit-label-input', function(e) {
            if (e.which == 13) {
                $(this).blur();
            }
        });
    </script>
@endpush
