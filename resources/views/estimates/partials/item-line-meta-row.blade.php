@php
    $colspanMeta = $invoiceSetting->hsn_sac_code_show ? 5 : 4;
    $fq = isset($freeQty) && $freeQty !== null && $freeQty !== '' ? $freeQty : '';
    $ed = $effDate ?? '';
    $xd = $expDate ?? '';
@endphp
<tr class="d-none d-md-table-row d-lg-table-row item-line-meta">
    <td colspan="{{ $colspanMeta }}" class="border-top-0 bg-additional-grey">
        <div class="row f-12 px-2 py-2 m-0">
            <div class="col-md-4 mb-2 mb-md-0">
                <label class="f-11 text-lightest d-block mb-1">@lang('modules.estimates.lineFreeQuantity')</label>
                <input type="text" name="item_free_quantity[]" class="form-control form-control-sm" value="{{ $fq }}">
            </div>
            <div class="col-md-4 mb-2 mb-md-0">
                <label class="f-11 text-lightest d-block mb-1">@lang('modules.estimates.lineEffectiveDate')</label>
                <input type="text" name="item_line_effective_date[]" class="form-control form-control-sm custom-date-picker item-line-effective-date" placeholder="@lang('placeholders.date')" value="{{ $ed }}">
            </div>
            <div class="col-md-4">
                <label class="f-11 text-lightest d-block mb-1">@lang('modules.estimates.lineExpiryDate')</label>
                <input type="text" name="item_line_expiry_date[]" class="form-control form-control-sm custom-date-picker item-line-expiry-date" placeholder="@lang('placeholders.date')" value="{{ $xd }}">
            </div>
        </div>
    </td>
</tr>
