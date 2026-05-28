@php
    $resolvedOrderTerms = \App\Support\CompanyDocumentTerms::resolveSaleOrderTerms($invoiceSetting ?? null);
@endphp
@if (trim($resolvedOrderTerms) !== '')
    <div class="word-break" style="margin-top: 10px;">
        <b>@lang('modules.invoiceSettings.saleOrderAndDeliveryOrderTerms')</b><br>{!! nl2br($resolvedOrderTerms) !!}
    </div>
@endif
