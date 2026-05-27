@php
    $purchaseSettingModel = $orderSetting ?? ($purchaseSetting ?? null);
    $resolvedPurchaseTerms = \App\Support\CompanyDocumentTerms::resolvePurchaseOrderTerms($purchaseSettingModel);
@endphp
@if (trim($resolvedPurchaseTerms) !== '')
    <div class="word-break" style="margin-top: 10px;">
        <b>@lang('purchase::modules.purchaseSettings.purchaseOrderTerms')</b><br>{!! nl2br($resolvedPurchaseTerms) !!}
    </div>
@endif
