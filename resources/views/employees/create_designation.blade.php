<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('app.menu.addDesignation')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
</div>
<div class="modal-body">

    <x-form id="save-designation-data-form">
        <div class="add-client bg-white rounded">

            <div class="row p-20">
                <div class="col-md-6">
                    <x-forms.text fieldId="designation_name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true" :fieldPlaceholder="__('placeholders.designation')">
                    </x-forms.text>
                </div>
                <div class="col-md-6">
                    <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('app.menu.parent_id')" fieldName="parent_label">
                    </x-forms.label>
                    <x-forms.input-group>
                        <select class="form-control select-picker" name="parent_id" id="parent_id" data-live-search="true">
                            <option value="">--</option>
                            @foreach ($designations as $designation)
                                <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                            @endforeach
                        </select>
                    </x-forms.input-group>
                </div>
            </div>



        </div>
    </x-form>


</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-designation-form" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $(document).ready(function() {
        $(".select-picker").selectpicker();
        $('#save-designation-form').click(function() {

            const url = "{{ route('designations.store') }}";

            var $btn = $('#save-designation-form');
            $btn.prop('disabled', true);
            $.easyBlockUI('#save-designation-data-form');
            window.apiHttp.postUrlEncoded(url, $('#save-designation-data-form').serialize()).then(function(response) {
                if (response.status === 'success') {
                    var options = [];
                    var rData = [];
                    rData = response.designations;

                    $.each(rData, function(index, value) {
                        var selectData = '<option value="">--</option>';
                        selectData = '<option value="' + value.id + '">' + value.name + '</option>';
                        options.push(selectData);
                    });

                    if ($(MODAL_LG).hasClass('show')) {
                        $(MODAL_LG).modal('hide');
                    }

                    $('#employee_designation').html(options);
                    $('#employee_designation').selectpicker('refresh');
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
                $.easyUnblockUI('#save-designation-data-form');
            });
        });

        init(RIGHT_MODAL);
    });
</script>
