@php
    $e = $estimate ?? null;
    $df = company()->date_format;
    $qDate = $e && $e->quotation_date ? $e->quotation_date->format($df) : '';
    $dDate = $e && $e->document_date ? $e->document_date->format($df) : '';
@endphp
<div class="col-12 mt-2 mb-2">
    <p class="f-14 text-dark-grey mb-0 font-weight-bold">@lang('modules.estimates.quotationSourceSection')</p>
    <p class="f-12 text-lightest mb-0">@lang('modules.estimates.quotationSourceSectionHelp')</p>
</div>
<div class="col-md-6 col-lg-4">
    <div class="form-group mb-4">
        <x-forms.label fieldId="quotation_date" :fieldLabel="__('modules.estimates.quotationDate')" />
        <input type="text" id="quotation_date" name="quotation_date" class="form-control height-35 f-15 custom-date-picker" placeholder="@lang('placeholders.date')" value="{{ $qDate }}">
    </div>
</div>
<div class="col-md-6 col-lg-4">
    <div class="form-group mb-4">
        <x-forms.label fieldId="document_date" :fieldLabel="__('modules.estimates.documentDate')" />
        <input type="text" id="document_date" name="document_date" class="form-control height-35 f-15 custom-date-picker" placeholder="@lang('placeholders.date')" value="{{ $dDate }}">
    </div>
</div>
<div class="col-md-6 col-lg-4">
    <div class="form-group mb-4">
        <x-forms.label fieldId="estimate_exchange_rate" :fieldLabel="__('modules.estimates.exchangeRate')" />
        <input type="text" name="exchange_rate" id="estimate_exchange_rate" class="form-control height-35 f-15" value="{{ $e && $e->exchange_rate !== null ? rtrim(rtrim(number_format((float) $e->exchange_rate, 6, '.', ''), '0'), '.') : '' }}" placeholder="1">
    </div>
</div>
<div class="col-md-6 col-lg-4">
    <div class="form-group mb-4">
        <x-forms.label fieldId="header_quotation_amount" :fieldLabel="__('modules.estimates.headerQuotationAmount')" />
        <input type="text" name="header_quotation_amount" id="header_quotation_amount" class="form-control height-35 f-15" value="{{ $e && $e->header_quotation_amount !== null ? $e->header_quotation_amount : '' }}">
    </div>
</div>
<div class="col-md-6 col-lg-4">
    <div class="form-group mb-4">
        <x-forms.label fieldId="header_tax_amount" :fieldLabel="__('modules.estimates.headerTaxAmount')" />
        <input type="text" name="header_tax_amount" id="header_tax_amount" class="form-control height-35 f-15" value="{{ $e && $e->header_tax_amount !== null ? $e->header_tax_amount : '' }}">
    </div>
</div>
<div class="col-md-6 col-lg-4">
    <div class="form-group mb-4">
        <x-forms.label fieldId="header_total_quantity" :fieldLabel="__('modules.estimates.headerTotalQuantity')" />
        <input type="text" name="header_total_quantity" id="header_total_quantity" class="form-control height-35 f-15" value="{{ $e && $e->header_total_quantity !== null ? $e->header_total_quantity : '' }}">
    </div>
</div>
<div class="col-md-12">
    <div class="form-group mb-4">
        <x-forms.label fieldId="delivery_note" :fieldLabel="__('modules.estimates.deliveryNote')" />
        <textarea name="delivery_note" id="delivery_note" rows="2" class="form-control f-14">{{ $e?->delivery_note ?? '' }}</textarea>
    </div>
</div>
<div class="col-md-6 col-lg-4">
    <div class="form-group mb-4">
        <x-forms.label fieldId="salesperson_name" :fieldLabel="__('modules.estimates.salespersonName')" />
        <input type="text" name="salesperson_name" id="salesperson_name" class="form-control height-35 f-15" value="{{ $e?->salesperson_name ?? '' }}">
    </div>
</div>
<div class="col-md-6 col-lg-4">
    <div class="form-group mb-4">
        <x-forms.label fieldId="tax_type_label" :fieldLabel="__('modules.estimates.taxTypeLabel')" />
        <input type="text" name="tax_type_label" id="tax_type_label" class="form-control height-35 f-15" value="{{ $e?->tax_type_label ?? '' }}">
    </div>
</div>
<div class="col-md-6 col-lg-4">
    <div class="form-group mb-4">
        <x-forms.label fieldId="payment_terms_code" :fieldLabel="__('modules.estimates.paymentTermsCode')" />
        <input type="text" name="payment_terms_code" id="payment_terms_code" class="form-control height-35 f-15" value="{{ $e?->payment_terms_code ?? '' }}">
    </div>
</div>
<div class="col-md-6 col-lg-4">
    <div class="form-group mb-4">
        <x-forms.label fieldId="payment_terms_name" :fieldLabel="__('modules.estimates.paymentTermsName')" />
        <input type="text" name="payment_terms_name" id="payment_terms_name" class="form-control height-35 f-15" value="{{ $e?->payment_terms_name ?? '' }}">
    </div>
</div>
<div class="col-md-6 col-lg-4">
    <div class="form-group mb-4">
        <x-forms.label fieldId="confirm_internal" :fieldLabel="__('modules.estimates.confirmInternal')" />
        <input type="text" name="confirm_internal" id="confirm_internal" class="form-control height-35 f-15" value="{{ $e?->confirm_internal ?? '' }}" maxlength="16">
    </div>
</div>
<div class="col-md-6 col-lg-4">
    <div class="form-group mb-4">
        <x-forms.label fieldId="confirm_customer" :fieldLabel="__('modules.estimates.confirmCustomer')" />
        <input type="text" name="confirm_customer" id="confirm_customer" class="form-control height-35 f-15" value="{{ $e?->confirm_customer ?? '' }}" maxlength="16">
    </div>
</div>
<div class="col-md-6 col-lg-4">
    <div class="form-group mb-4">
        <x-forms.label fieldId="price_terms" :fieldLabel="__('modules.estimates.priceTerms')" />
        <input type="text" name="price_terms" id="price_terms" class="form-control height-35 f-15" value="{{ $e?->price_terms ?? '' }}">
    </div>
</div>
<div class="col-md-6 col-lg-4">
    <div class="form-group mb-4">
        <x-forms.label fieldId="volume_unit" :fieldLabel="__('modules.estimates.volumeUnit')" />
        <input type="text" name="volume_unit" id="volume_unit" class="form-control height-35 f-15" value="{{ $e?->volume_unit ?? '' }}">
    </div>
</div>
<div class="col-md-6 col-lg-4">
    <div class="form-group mb-4">
        <x-forms.label fieldId="total_gross_weight_kg" :fieldLabel="__('modules.estimates.totalGrossWeightKg')" />
        <input type="text" name="total_gross_weight_kg" id="total_gross_weight_kg" class="form-control height-35 f-15" value="{{ $e && $e->total_gross_weight_kg !== null ? $e->total_gross_weight_kg : '' }}">
    </div>
</div>
<div class="col-md-6 col-lg-4">
    <div class="form-group mb-4">
        <x-forms.label fieldId="total_volume" :fieldLabel="__('modules.estimates.totalVolume')" />
        <input type="text" name="total_volume" id="total_volume" class="form-control height-35 f-15" value="{{ $e && $e->total_volume !== null ? $e->total_volume : '' }}">
    </div>
</div>
