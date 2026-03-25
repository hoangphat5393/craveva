<div class="row" id="import_table">
    <div class="col-sm-12">
        <x-form id="import-purchase-inventory-data-form">
            <div class="add-product bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('app.importExcel') @lang('purchase::app.menu.inventory')</h4>
                <div class="col-sm-12 pt-2">
                    <div class="alert alert-warning" role="alert">
                        @lang('app.importProjectExcelInfo')
                    </div>
                </div>
                <div class="row py-20">
                    <div class="col-md-12">
                        <x-forms.file :fieldLabel="__('modules.import.file')" fieldName="import_file" fieldId="purchase_inventory_import" />
                    </div>
                    <div class="col-md-12">
                        <x-forms.toggle-switch class="mr-0 mr-lg-12" :fieldLabel="__('modules.import.containsHeadings')" fieldName="heading" fieldId="heading" />
                        <x-forms.toggle-switch class="mr-0 mr-lg-12" :fieldLabel="__('modules.import.skipFooterRow')" fieldName="skip_footer" fieldId="skip_footer" />
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="import-purchase-inventory-form" class="mr-3" icon="arrow-right">@lang('app.uploadNext')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('purchase-inventory.index')" class="border-0">@lang('app.back')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $("#purchase_inventory_import").dropify({
            messages: dropifyMessages
        });

        $('body').on('click', '#import-purchase-inventory-form', function() {
            const url = "{{ route('purchase-inventory.import.store') }}";
            const $btn = $('#import-purchase-inventory-form');
            const formEl = document.getElementById('import-purchase-inventory-data-form');

            $btn.prop('disabled', true);
            $.easyBlockUI('#import_table');
            window.apiHttp.postForm(url, formEl).then(function(response) {
                if (response.status == 'success') {
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
                $btn.prop('disabled', false);
                $.easyUnblockUI('#import_table');
            });
        });
    });
</script>
