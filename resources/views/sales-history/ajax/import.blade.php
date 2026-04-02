<div class="row" id="import_table">
    <div class="col-sm-12">
        <x-form id="import-sales-history-data-form">
            <div class="add-product bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                    @lang('app.importExcel') @lang('app.menu.salesHistory')
                </h4>
                <div class="col-sm-12 pt-2">
                    <div class="alert alert-info" role="alert">
                        @lang('app.salesHistoryImportBlurb')
                    </div>
                    <div class="alert alert-warning" role="alert">
                        @lang('app.importProjectExcelInfo')
                    </div>
                </div>
                <div class="row py-20">
                    <div class="col-md-12">
                        <x-forms.file :fieldLabel="__('modules.import.file')" fieldName="import_file" fieldId="sales_history_import" />
                    </div>
                    <div class="col-md-12">
                        <x-forms.toggle-switch class="mr-0 mr-lg-12" :fieldLabel="__('modules.import.containsHeadings')" fieldName="heading" fieldId="heading" />
                        <x-forms.toggle-switch class="mr-0 mr-lg-12" :fieldLabel="__('modules.import.skipFooterRow')" fieldName="skip_footer" fieldId="skip_footer" />
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="import-sales-history-form" class="mr-3" icon="arrow-right">@lang('app.uploadNext')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('sales-history.index')" class="border-0">@lang('app.back')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $("#sales_history_import").dropify({
            messages: dropifyMessages
        });

        $('body').on('click', '#import-sales-history-form', function() {
            const url = "{{ route('sales-history.import.store') }}";
            const $btn = $('#import-sales-history-form');
            $btn.prop('disabled', true);
            $.easyBlockUI('#import-sales-history-data-form');
            window.apiHttp.postForm(url, document.getElementById('import-sales-history-data-form')).then(function(response) {
                if (response.status == 'success') {
                    $('#import_table').html(response.view);
                }
            }).catch(function(err) {
                $.handleApiFormError(err);
            }).finally(function() {
                $btn.prop('disabled', false);
                $.easyUnblockUI('#import-sales-history-data-form');
            });
        });
    });
</script>
