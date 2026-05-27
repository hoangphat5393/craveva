@php
    $purchaseSettingModel = $orderSetting ?? ($purchaseSetting ?? null);
    $resolvedGrnTerms = \App\Support\CompanyDocumentTerms::resolveGrnTerms($purchaseSettingModel);
@endphp
@if (trim($resolvedGrnTerms) !== '')
    <div class="word-break" style="margin-top: 10px;">
        <b>@lang('purchase::modules.purchaseSettings.grnTerms')</b><br>{!! nl2br($resolvedGrnTerms) !!}
    </div>
@endif
