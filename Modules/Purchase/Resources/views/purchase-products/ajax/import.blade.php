<div class="row" id="import_table">
    <div class="col-sm-12">
        <x-form id="import-purchase-product-data-form">
            <div class="bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('app.importproducts')</h4>
                <div class="col-sm-12 pt-2">
                    <div class="alert alert-warning" role="alert">
                        @lang('app.importProjectExcelInfo')
                    </div>
                </div>
                <div class="row py-20">
                    <div class="col-md-12">
                        <x-forms.file :fieldLabel="__('modules.import.file')" fieldName="import_file" fieldId="purchase_product_import" />
                    </div>
                    <div class="col-md-12">
                        <x-forms.toggle-switch class="mr-0 mr-lg-12" :fieldLabel="__('modules.import.containsHeadings')" fieldName="heading" fieldId="heading" />
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="import-purchase-product-form" class="mr-3" icon="arrow-right">@lang('app.uploadNext')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('purchase-products.index')" class="border-0">@lang('app.back')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $("#purchase_product_import").dropify({
            messages: dropifyMessages
        });

        $('body').on('click', '#import-purchase-product-form', function() {
            const url = "{{ route('purchase-products.import.store') }}";
            const $btn = $('#import-purchase-product-form');
            const formEl = document.getElementById('import-purchase-product-data-form');

            $btn.prop('disabled', true);
            $.easyBlockUI('#import_table');
            window.apiHttp.postForm(url, formEl)
                .then(function(response) {
                    if (response.status == 'success') {
                        $('#import_table').html(response.view);
                    }
                })
                .catch(function(err) {
                    $.handleApiFormError(err);
                })
                .finally(function() {
                    $btn.prop('disabled', false);
                    $.easyUnblockUI('#import_table');
                });
        });
    });
</script>
