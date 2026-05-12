@extends('layouts.app')

@section('content')
    <div class="w-100 d-flex">

        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <nav class="tabs px-4 border-bottom-grey">
                        <div class="nav" id="nav-tab" role="tablist">
                            <span class="nav-item nav-link f-15 active" role="tab" aria-selected="true">@lang('modules.orders.apiTab')</span>
                        </div>
                    </nav>
                </div>
            </x-slot>

            <div class="p-4">
                <p class="text-dark-grey f-14 mb-4">@lang('modules.orders.apiIntegrationIntro')</p>

                <x-alert type="warning" icon="exclamation-triangle" class="mb-4">
                    <h6 class="f-14 font-weight-bold mb-2">@lang('modules.orders.apiRingfenceTitle')</h6>
                    <p class="f-13 mb-2 mb-0">@lang('modules.orders.apiRingfenceIntro')</p>
                    <ul class="f-13 mb-0 pl-3">
                        <li class="mb-1">@lang('modules.orders.apiRingfenceBullet1')</li>
                        <li class="mb-1">@lang('modules.orders.apiRingfenceBullet2')</li>
                        <li>@lang('modules.orders.apiRingfenceBullet3')</li>
                    </ul>
                </x-alert>

                <div class="mb-4">
                    <h6 class="f-14 font-weight-bold text-dark mb-2">@lang('modules.orders.apiFillAiTitle')</h6>
                    <ol class="f-13 text-dark-grey pl-3 mb-0">
                        <li class="mb-2">@lang('modules.orders.apiFillAiStep1')</li>
                        <li class="mb-2">@lang('modules.orders.apiFillAiStep2')</li>
                        <li class="mb-2">@lang('modules.orders.apiFillAiStep3')</li>
                        <li>@lang('modules.orders.apiFillAiStep4')</li>
                    </ol>
                </div>

                <div class="mb-4">
                    <h6 class="f-14 font-weight-bold text-dark mb-2">@lang('modules.orders.apiPayloadMinimumTitle')</h6>
                    <ul class="f-13 text-dark-grey pl-3 mb-2">
                        <li class="mb-1">@lang('modules.orders.apiPayloadFieldCompanyId')</li>
                        <li class="mb-1">@lang('modules.orders.apiPayloadFieldClientId')</li>
                        <li class="mb-1">@lang('modules.orders.apiPayloadFieldExternalEventId')</li>
                        <li class="mb-1">@lang('modules.orders.apiPayloadFieldItems')</li>
                    </ul>
                    <p class="f-12 text-lightest mb-0">@lang('modules.orders.apiAcceptHeaderBody')</p>
                </div>

                @if (!$aiOrderWebhookSecretConfigured)
                    <x-alert type="danger" icon="exclamation-circle">
                        @lang('modules.orders.apiSecretMissing')
                    </x-alert>
                @endif

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <x-forms.text fieldId="ai_webhook_base_url" :fieldLabel="__('modules.orders.apiBaseUrl')" fieldName="ai_webhook_base_url" :fieldValue="$aiOrderWebhookBaseUrl" fieldReadOnly="true" />
                        <p class="f-12 text-lightest mt-1">@lang('modules.orders.apiBaseUrlVersionPathHint')</p>
                    </div>
                    <div class="col-md-12 mb-3">
                        <x-forms.text fieldId="ai_webhook_company_id" :fieldLabel="__('modules.orders.apiCompanyId')" fieldName="ai_webhook_company_id" :fieldValue="(string) $aiOrderWebhookCompanyId" fieldReadOnly="true" />
                        @if ($aiOrderWebhookCompanyName !== '')
                            <p class="f-12 text-dark-grey font-weight-semibold mt-1">{{ __('modules.orders.apiCompanyIdSelected', ['name' => $aiOrderWebhookCompanyName]) }}</p>
                        @endif
                        <p class="f-12 text-lightest mt-1">@lang('modules.orders.apiCompanyIdHelp')</p>
                    </div>
                    @if ($aiOrderWebhookUrl)
                        <div class="col-md-12 mb-3">
                            <x-forms.text fieldId="ai_webhook_post_url" :fieldLabel="__('modules.orders.apiWebhookPostUrl')" fieldName="ai_webhook_post_url" :fieldValue="$aiOrderWebhookUrl" fieldReadOnly="true" />
                        </div>
                        <div class="col-md-12 mb-3">
                            <x-forms.text fieldId="ai_webhook_header" :fieldLabel="__('modules.orders.apiWebhookHeader')" fieldName="ai_webhook_header" :fieldValue="$aiOrderWebhookHeaderLine" fieldReadOnly="true" />
                            <p class="f-12 text-lightest mt-1">@lang('modules.orders.apiWebhookHeaderHelp')</p>
                        </div>
                    @endif
                </div>

                <div class="mb-4">
                    <h6 class="f-14 font-weight-bold text-dark mb-2">@lang('modules.orders.apiCurlExampleTitle')</h6>
                    @if (!empty($aiOrderWebhookCurlExample))
                        <p class="f-12 text-lightest mb-2">@lang('modules.orders.apiCurlCopyHint')</p>
                        <pre class="f-12 p-3 bg-additional-grey rounded border-grey text-dark mb-0" style="white-space: pre-wrap; word-break: break-all;">{{ $aiOrderWebhookCurlExample }}</pre>
                    @else
                        <p class="f-13 text-dark-grey mb-0">@lang('modules.orders.apiCurlExampleMissingSecret')</p>
                    @endif
                </div>

                <x-alert type="info" icon="info-circle" class="mb-3">
                    @lang('modules.orders.apiExternalEventIdHint')
                </x-alert>

                <p class="f-13 text-dark-grey mb-2">
                    @lang('modules.orders.apiHttpCodesHint')
                </p>

                <p class="f-13 text-dark-grey mb-0">
                    @lang('modules.orders.apiDocHint')
                </p>
            </div>
        </x-setting-card>
    </div>
@endsection
