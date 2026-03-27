<div class="row" id="import_table">
    <div class="col-sm-12">
        <x-form id="import-project-data-form">
            <div class="add-project bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('app.importProjects')</h4>
                <div class="col-sm-12 pt-2">
                    <div class="alert alert-warning" role="alert">
                        @lang('app.importProjectExcelInfo')
                    </div>
                </div>
                <div class="row py-20">
                    <div class="col-md-12">
                        <x-forms.file :fieldLabel="__('modules.import.file')" fieldName="import_file" fieldId="project_import" />
                    </div>
                    <div class="col-md-12">
                        <x-forms.toggle-switch class="mr-0 mr-lg-12"
                            :fieldLabel="__('modules.import.containsHeadings')"
                            fieldName="heading"
                            fieldId="heading"/>
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="import-project-form" class="mr-3" icon="arrow-right">@lang('app.uploadNext')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('projects.index')" class="border-0">@lang('app.back')
                    </x-forms.button-cancel>

                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script>

    $(document).ready(function() {

        $("#project_import").dropify({
            messages: dropifyMessages
        });

        $('body').on('click', '#import-project-form', function() {
            const url = "{{ route('projects.import.store') }}";
            var $btn = $('#import-project-form');
            var prev = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + (document.loading || 'Loading...'));
            $.easyBlockUI('#import-project-data-form');
            window.apiHttp.postForm(url, document.getElementById('import-project-data-form')).then(function(response) {
                if (response.status == 'success') {
                    $('#import_table').html(response.view);
                }
            }).catch(function(err) {
                $.handleApiFormError(err);
            }).finally(function() {
                $.easyUnblockUI('#import-project-data-form');
                $btn.prop('disabled', false).html(prev);
            });
        });
    });
</script>
