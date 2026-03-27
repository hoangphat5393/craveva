<div class="row" id="import_table">
    <div class="col-sm-12">
        <x-form id="import-attendance-data-form">
            <div class="add-attendance bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('app.importAttendance')</h4>
                <div class="col-sm-12 pt-2">
                    <div class="alert alert-warning" role="alert">
                        @lang('app.importAttendanceExcelInfo')
                    </div>
                </div>
                <div class="row py-20">
                    <div class="col-md-12">
                        <x-forms.file :fieldLabel="__('modules.import.file')" fieldName="import_file" fieldId="attendance_import" />
                    </div>
                    <div class="col-md-12">
                        <x-forms.toggle-switch class="mr-0 mr-lg-12" :fieldLabel="__('modules.import.containsHeadings')" fieldName="heading" fieldId="heading" />
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="import-attendance-form" class="mr-3" icon="arrow-right">@lang('app.uploadNext')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('attendances.index')" class="border-0">@lang('app.back')
                    </x-forms.button-cancel>

                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function() {

        $("#attendance_import").dropify({
            messages: dropifyMessages
        });

        $('body').on('click', '#import-attendance-form', function() {
            const url = "{{ route('attendances.import.store') }}";

            var $impBtn = $('#import-attendance-form');
            $impBtn.prop('disabled', true);
            $.easyBlockUI('#import-attendance-data-form');
            window.apiHttp.postForm(url, document.getElementById('import-attendance-data-form')).then(function(response) {
                if (response.status === 'success') {
                    $('#import_table').html(response.view);
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
                $impBtn.prop('disabled', false);
                $.easyUnblockUI('#import-attendance-data-form');
            });
        });
    });
</script>
