{{-- Included inside parent <script> — do not wrap in <script> tags. --}}
(function() {
const purchaseProductValidationMessages = {
nameRequired: @json(__('validation.required', ['attribute' => __('app.name')])),
sellingPriceRequired: @json(__('purchase::messages.sellingPriceRequired')),
purchasePriceRequired: @json(__('purchase::messages.purchasePriceRequired')),
openingStockRequired: @json(__('purchase::messages.openingStockRequired')),
unitConversionCannotMatchBase: @json(__('purchase::messages.unitConversionCannotMatchBase')),
unitConversionDuplicateUnit: @json(__('purchase::messages.unitConversionDuplicateUnit')),
unitConversionFactorInvalid: @json(__('purchase::messages.unitConversionFactorInvalid')),
invalidDataToast: @json(__('purchase::app.productFormInvalidData')),
};

function purchaseProductClearFieldErrors($form) {
$form.find('.is-invalid').removeClass('is-invalid');
$form.find('.invalid-feedback.purchase-product-client-error').remove();
}

function purchaseProductMarkFieldError($form, selector, message) {
const $input = $form.find(selector).first();
if (!$input.length) {
return;
}

$input.addClass('is-invalid');
let $host = $input.closest('.form-group');
if (!$host.length) {
$host = $input.closest('.input-group').parent();
}
if (!$host.length) {
$host = $input.parent();
}

$host.find('.invalid-feedback.purchase-product-client-error').remove();
$('<div class="invalid-feedback d-block purchase-product-client-error"></div>')
.text(message)
.appendTo($host);
}

function purchaseProductBaseUnitId($form) {
const $unitField = $form.find('#unit_type_id').first();
let v = $unitField.val();
if (Array.isArray(v)) {
v = v[0];
}
if ((!v || v === '') && typeof $unitField.selectpicker === 'function') {
v = $unitField.selectpicker('val');
if (Array.isArray(v)) {
v = v[0];
}
}

return v ? parseInt(v, 10) : 0;
}

function purchaseProductValidateUnitConversions($form, fieldErrors) {
const $section = $form.find('#product-unit-conversions-section');
const productType = ($form.find('#type').val() || '').toString();

if (
!$section.length
|| $section.hasClass('d-none')
|| (typeof window.purchaseProductTypeSupportsAlternateUom === 'function'
&& !window.purchaseProductTypeSupportsAlternateUom(productType))
) {
return;
}

const baseUnitId = purchaseProductBaseUnitId($form);
const seenUnits = {};

$form.find('#product-unit-conversions-body tr').each(function() {
const $row = $(this);
const $unitSelect = $row.find('.unit-conversion-unit-select');
const unitId = parseInt($unitSelect.val(), 10);

if (!unitId) {
return;
}

if (unitId === baseUnitId) {
fieldErrors.push({
selector: $unitSelect,
message: purchaseProductValidationMessages.unitConversionCannotMatchBase,
});

return;
}

if (seenUnits[unitId]) {
fieldErrors.push({
selector: $unitSelect,
message: purchaseProductValidationMessages.unitConversionDuplicateUnit,
});

return;
}

seenUnits[unitId] = true;

const factor = parseFloat($row.find('.unit-conversion-factor').val());
if (!Number.isFinite(factor) || factor <= 0) { fieldErrors.push({ selector: $row.find('.unit-conversion-factor'), message: purchaseProductValidationMessages.unitConversionFactorInvalid, }); } }); } function purchaseProductShowClientInvalidToast() { if (typeof Swal==='undefined' ) { return; } Swal.fire({ icon: 'error' , text: purchaseProductValidationMessages.invalidDataToast, toast: true,
    position: 'top-end' , timer: 4500, showConfirmButton: false, }); } window.validatePurchaseProductForm=function(formSelector) { const $form=$(formSelector); if (!$form.length) { return true; } purchaseProductClearFieldErrors($form); const fieldErrors=[]; const productType=($form.find('#type').val() || '' ).toString(); const isService=productType==='service' ; const
    name=($form.find('#name').val() || '' ).toString().trim(); if (name==='' ) { fieldErrors.push({ selector: '#name' , message: purchaseProductValidationMessages.nameRequired, }); } if (isService) { const sellingRaw=($form.find('#selling_price').val() || '' ).toString().trim(); const sellingPrice=sellingRaw==='' ? NaN : parseFloat(sellingRaw); if (!Number.isFinite(sellingPrice)) {
    fieldErrors.push({ selector: '#selling_price' , message: purchaseProductValidationMessages.sellingPriceRequired, }); } } else { const usesCostOnlyPricing=typeof window.purchaseProductTypeUsesCostUom==='function' && window.purchaseProductTypeUsesCostUom(productType); if (!usesCostOnlyPricing) { const sellingRaw=($form.find('#selling_price').val() || '' ).toString().trim(); const
    sellingPrice=sellingRaw==='' ? NaN : parseFloat(sellingRaw); if (!Number.isFinite(sellingPrice)) { fieldErrors.push({ selector: '#selling_price' , message: purchaseProductValidationMessages.sellingPriceRequired, }); } } const hideCostPrice=typeof window.purchaseProductTypeHidesCostPrice==='function' && window.purchaseProductTypeHidesCostPrice(productType); const
    customCostFromBom=$form.find('#cost_from_bom').prop('checked'); if (!hideCostPrice && !customCostFromBom) { const purchaseRaw=($form.find('#purchase_price').val() || '' ).toString().trim(); const purchasePrice=purchaseRaw==='' ? NaN : parseFloat(purchaseRaw); if (!Number.isFinite(purchasePrice) || purchasePrice <=0) { fieldErrors.push({ selector: '#purchase_price' , message:
    purchaseProductValidationMessages.purchasePriceRequired, }); } } if ($form.find('#track_inventory').prop('checked')) { const stockRaw=($form.find('#opening_stock').val() || '' ).toString().trim(); const openingStock=stockRaw==='' ? NaN : parseFloat(stockRaw); if (!Number.isFinite(openingStock)) { fieldErrors.push({ selector: '#opening_stock' , message:
    purchaseProductValidationMessages.openingStockRequired, }); } } } purchaseProductValidateUnitConversions($form, fieldErrors); if (fieldErrors.length===0) { return true; } fieldErrors.forEach(function(err) { purchaseProductMarkFieldError($form, err.selector, err.message); }); purchaseProductShowClientInvalidToast(); const $firstInvalid=$form.find('.is-invalid').first(); if
    ($firstInvalid.length) { $('html, body').animate({ scrollTop: Math.max(0, $firstInvalid.offset().top - 120), }, 200); $firstInvalid.trigger('focus'); } return false; }; window.preparePurchaseProductFormForSubmit=function($form) { $form.find('select.select-picker').each(function() { const $select=$(this); if (typeof $select.selectpicker==='function' ) { $select.val($select.selectpicker('val'));
    } }); }; window.submitPurchaseProductForm=function(options) { const $form=$(options.formSelector); const url=options.url; if (!window.validatePurchaseProductForm(options.formSelector)) { return; } window.preparePurchaseProductFormForSubmit($form); window.apiHttp.postUrlEncoded(url, $form.serialize()) .then(options.onSuccess) .catch(function(err) { $.handleApiFormError(err); }); }; })();
