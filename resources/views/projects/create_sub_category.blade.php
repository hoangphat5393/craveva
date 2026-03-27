<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.client.projectSubCategory')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-table class="table-bordered client-subcat-table" headType="thead-light">
        <x-slot name="thead">
            <th>#</th>
            <th>@lang('modules.productCategory.subCategory')</th>
            <th>@lang('modules.productCategory.category')</th>
            <th class="text-right">@lang('app.action')</th>
        </x-slot>

        @forelse($subcategories as $key=>$subcategory)
            <tr id="cat-{{ $subcategory->id }}">
                <td>{{ $key + 1 }}</td>
                <td data-row-id="{{ $subcategory->id }}" contenteditable="true">
                    {{ $subcategory->category_name }}</td>
                <td>{{ $subcategory->projectCategory->category_name }}</td>

                <td class="text-right">
                    @if ($deletePermission == 'all' || $deletePermission == 'added')
                        <x-forms.button-secondary data-cat-id="{{ $subcategory->id }}" icon="trash"
                            class="delete-category">
                            @lang('app.delete')
                        </x-forms.button-secondary>
                    @endif
                </td>
            </tr>
        @empty
            <x-cards.no-record-found-list colspan="4"/>

        @endforelse
    </x-table>

    <x-form id="createProjectCategory">
        <div class="row border-top-grey ">
            <div class="col-sm-12 col-md-6">
                <x-forms.select fieldId="create_category_id" :fieldLabel="__('modules.client.clientCategory')"
                    fieldName="category_id" search="true" fieldRequired="true">
                    @forelse($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                    @empty
                        <option value="">@lang('messages.noCategoryAdded')</option>
                    @endforelse
                </x-forms.select>
            </div>
            <div class="col-sm-12 col-md-6">
                <x-forms.text fieldId="category_name" :fieldLabel="__('modules.projectCategory.categoryName')"
                    fieldName="category_name" fieldRequired="true" :fieldPlaceholder="__('placeholders.categoryName')">
                </x-forms.text>
            </div>
            <input type="hidden" name="selected_category" id="selected_category" value="">

        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-category" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    init(MODAL_LG);
    $('.delete-category').click(function() {

        var id = $(this).data('cat-id');
        var url = "{{ route('ProjectSubCategory.destroy', ':id') }}";
        url = url.replace(':id', id);
        let selectedCategory = $('#project_category_id').val();
        let selectedSubCategory = $('#sub_category_id').val();

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
                var delBody = '_token=' + encodeURIComponent("{{ csrf_token() }}") + '&_method=DELETE&selectedCategory=' + encodeURIComponent(selectedCategory);
                window.apiHttp.postUrlEncoded(url, delBody).then(function(response) {
                    if (response.status == "success") {
                        $('#cat-' + id).fadeOut();
                        var options = [];
                        var rData = [];
                        rData = response.data;
                        $.each(rData, function(index, value) {
                            var selectData = '';
                            var isSelected = (value.id == selectedSubCategory) ? 'selected' : '';
                            selectData = '<option value="' + value.id + '" ' + isSelected + '>' +
                                value
                                .category_name + '</option>';
                            options.push(selectData);
                        });

                        $('#project_category_id').val(selectedCategory);
                        $('#sub_category_id').html('<option value="">--</option>' +
                            options);
                        $('#sub_category_id').selectpicker('refresh');
                    }
                }).catch(function(err) {
                    $.handleApiFormError(err);
                });
            }
        });

    });

    $('#save-category').click(function() {
        var url = "{{ route('ProjectSubCategory.store') }}";
        let selectedCategory = $('#project_category_id').val();
        let selectedSubCategory = $('#sub_category_id').val();
        $('#selected_category').val(selectedCategory);
        window.apiHttp.postUrlEncoded(url, $('#createProjectCategory').serialize()).then(function(response) {
            if (response.status == 'success') {
                var options = [];
                var rData = [];
                rData = response.data;
                console.log(rData);
                $.each(rData, function(index, value) {
                    var selectData = '';
                    var isSelected = (value.id == selectedSubCategory) ? 'selected' : '';
                    selectData = '<option value="' + value.id + '" ' + isSelected + '>' + value
                        .category_name + '</option>';
                    options.push(selectData);
                });

                $('#project_category_id').val(selectedCategory);
                console.log(options);
                $('#sub_category_id').html('<option value="">--</option>' + options);
                $('#sub_category_id').selectpicker('refresh');
                $(MODAL_LG).modal('hide');
            }
        }).catch(function(err) {
            $.handleApiFormError(err);
        });
    });

    $('.client-subcat-table [contenteditable=true]').focus(function() {
        $(this).data("initialText", $(this).html());
        let rowId = $(this).data('row-id');
    }).blur(function() {
        // ...if content is different...
        if ($(this).data("initialText") !== $(this).html()) {
            let id = $(this).data('row-id');
            let value = $(this).html();

            var url = "{{ route('ProjectSubCategory.update', ':id') }}";
            url = url.replace(':id', id);
            let selectedCategory = $('#project_category_id').val();
            let selectedSubCategory = $('#sub_category_id').val();

            $.easyBlockUI('#cat-' + id);
            window.apiHttp.postUrlEncoded(url, {
                category_name: value,
                _token: "{{ csrf_token() }}",
                _method: 'PUT',
                selectedCategory: selectedCategory
            }).then(function(response) {
                if (response.status == 'success') {
                    var options = [];
                    var rData = [];
                    rData = response.data;
                    $.each(rData, function(index, value) {
                        var selectData = '';
                        var isSelected = (value.id == selectedSubCategory) ? 'selected' : '';
                        selectData = '<option value="' + value.id + '" ' + isSelected + '>' + value
                            .category_name + '</option>';
                        options.push(selectData);
                    });

                    $('#project_category_id').val(selectedCategory);
                    $('#sub_category_id').html('<option value="">--</option>' + options);
                    $('#sub_category_id').selectpicker('refresh');
                }
            }).catch(function(err) {
                $.handleApiFormError(err);
            }).finally(function() {
                $.easyUnblockUI('#cat-' + id);
            })
        }
    });
</script>
