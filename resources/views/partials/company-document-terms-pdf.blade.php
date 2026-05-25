@php
    $resolvedOrderTerms = trim((string) ($invoiceSetting->order_terms ?? '')) !== '' ? (string) $invoiceSetting->order_terms : (string) ($invoiceSetting->invoice_terms ?? '');
@endphp
@if (trim($resolvedOrderTerms) !== '')
    <div class="word-break" style="margin-top: 10px;">
        <b>@lang('modules.invoiceSettings.orderTerms')</b><br>{!! nl2br($resolvedOrderTerms) !!}
    </div>
@endif
