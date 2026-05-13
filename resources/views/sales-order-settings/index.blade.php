@extends('layouts.app')

@section('content')
    <div class="w-100 d-flex">

        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card :withoutForm="true">
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
                        <li class="mb-1">@lang('modules.orders.apiPayloadFieldClientCode')</li>
                        <li class="mb-1">@lang('modules.orders.apiPayloadFieldExternalEventId')</li>
                        <li class="mb-1">@lang('modules.orders.apiPayloadFieldItems')</li>
                    </ul>
                    <p class="f-12 text-lightest mb-0">@lang('modules.orders.apiAcceptHeaderBody')</p>
                </div>

                @if (session('success'))
                    <x-alert type="success" icon="check-circle" class="mb-3">
                        {{ session('success') }}
                    </x-alert>
                @endif

                @if (!$aiOrderWebhookSecretConfigured)
                    <x-alert type="danger" icon="exclamation-circle" class="mb-3">
                        @lang('modules.orders.apiNoWebhookSecret')
                    </x-alert>
                @elseif (!empty($aiOrderWebhookUsingLegacyGlobalFallback))
                    <x-alert type="info" icon="info-circle" class="mb-3">
                        @lang('modules.orders.apiUsingLegacyGlobalSecretHint')
                    </x-alert>
                @endif

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="ai_webhook_base_url" :fieldLabel="__('modules.orders.apiBaseUrl')" fieldRequired="false" />
                            <div class="input-group">
                                <input type="text" class="form-control height-35 f-14" id="ai_webhook_base_url" name="ai_webhook_base_url" value="{{ $aiOrderWebhookBaseUrl }}" readonly>
                                <div class="input-group-append">
                                    <a href="javascript:;" class="btn btn-secondary btn-copy height-35 f-12 d-flex align-items-center px-3 border-left-0" data-clipboard-target="#ai_webhook_base_url" role="button">
                                        <i class="fa fa-copy mr-1"></i>@lang('app.copy')
                                    </a>
                                </div>
                            </div>
                        </div>
                        <p class="f-12 text-lightest mt-1">@lang('modules.orders.apiBaseUrlVersionPathHint')</p>
                    </div>
                    <div class="col-md-12 mb-3">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="ai_webhook_company_id" :fieldLabel="__('modules.orders.apiCompanyId')" fieldRequired="false" />
                            <div class="input-group">
                                <input type="text" class="form-control height-35 f-14" id="ai_webhook_company_id" name="ai_webhook_company_id" value="{{ (string) $aiOrderWebhookCompanyId }}" readonly>
                                <div class="input-group-append">
                                    <a href="javascript:;" class="btn btn-secondary btn-copy height-35 f-12 d-flex align-items-center px-3 border-left-0" data-clipboard-target="#ai_webhook_company_id" role="button">
                                        <i class="fa fa-copy mr-1"></i>@lang('app.copy')
                                    </a>
                                </div>
                            </div>
                        </div>
                        @if ($aiOrderWebhookCompanyName !== '')
                            <p class="f-12 text-dark-grey font-weight-semibold mt-1">{{ __('modules.orders.apiCompanyIdSelected', ['name' => $aiOrderWebhookCompanyName]) }}</p>
                        @endif
                        <p class="f-12 text-lightest mt-1">@lang('modules.orders.apiCompanyIdHelp')</p>
                        <form method="post" action="{{ route('sales-order-settings.regenerate-webhook-secret') }}" class="mt-2 d-inline-block">
                            @csrf
                            <button type="submit" class="btn btn-primary rounded f-14 p-2">
                                <i class="fa fa-key mr-1"></i> @lang('modules.orders.apiRegenerateWebhookButton')
                            </button>
                        </form>
                        <p class="f-12 text-lightest mt-2 mb-0">@lang('modules.orders.apiRegenerateWebhookHelp')</p>
                    </div>
                    @if ($aiOrderWebhookUrl)
                        <div class="col-md-12 mb-3">
                            <div class="form-group my-3">
                                <x-forms.label fieldId="ai_webhook_post_url" :fieldLabel="__('modules.orders.apiWebhookPostUrl')" fieldRequired="false" />
                                <div class="input-group">
                                    <input type="text" class="form-control height-35 f-14" id="ai_webhook_post_url" name="ai_webhook_post_url" value="{{ $aiOrderWebhookUrl }}" readonly>
                                    <div class="input-group-append">
                                        <a href="javascript:;" class="btn btn-secondary btn-copy height-35 f-12 d-flex align-items-center px-3 border-left-0" data-clipboard-target="#ai_webhook_post_url" role="button">
                                            <i class="fa fa-copy mr-1"></i>@lang('app.copy')
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-group my-3">
                                <x-forms.label fieldId="ai_webhook_header" :fieldLabel="__('modules.orders.apiWebhookHeader')" fieldRequired="false" />
                                <div class="input-group">
                                    <input type="text" class="form-control height-35 f-14" id="ai_webhook_header" name="ai_webhook_header" value="{{ $aiOrderWebhookHeaderLine }}" readonly>
                                    <div class="input-group-append">
                                        <a href="javascript:;" class="btn btn-secondary btn-copy height-35 f-12 d-flex align-items-center px-3 border-left-0" data-clipboard-target="#ai_webhook_header" role="button">
                                            <i class="fa fa-copy mr-1"></i>@lang('app.copy')
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <p class="f-12 text-lightest mt-1">@lang('modules.orders.apiWebhookHeaderHelp')</p>
                        </div>
                    @endif
                </div>

                <div class="mb-4">
                    <h6 class="f-14 font-weight-bold text-dark mb-2">@lang('modules.orders.apiCurlExampleTitle')</h6>
                    @if (!empty($aiOrderWebhookCurlExample))
                        <p class="f-12 text-lightest mb-2">@lang('modules.orders.apiCurlCopyHint')</p>
                        <textarea id="ai_webhook_curl_clipboard" readonly class="position-fixed border-0 p-0 m-0 overflow-hidden" style="top: -2000px; left: 0; width: 1px; height: 1px; opacity: 0; z-index: -1;" aria-hidden="true" tabindex="-1">{{ $aiOrderWebhookCurlExample }}</textarea>
                        <div class="position-relative">
                            <pre class="f-12 p-3 bg-additional-grey rounded border-grey text-dark mb-0 pr-5" style="white-space: pre-wrap; word-break: break-all;">{{ $aiOrderWebhookCurlExample }}</pre>
                            <a href="javascript:;" class="btn-copy btn-secondary f-12 rounded px-2 py-1 position-absolute" style="top: 8px; right: 8px;" data-clipboard-target="#ai_webhook_curl_clipboard" role="button">
                                <i class="fa fa-copy mr-1"></i>@lang('app.copy')
                            </a>
                        </div>
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

@push('scripts')
    <script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof ClipboardJS === 'undefined') {
                return;
            }
            var clipboard = new ClipboardJS('.btn-copy');
            clipboard.on('success', function(e) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        text: @json(__('app.copied')),
                        toast: true,
                        position: 'top-end',
                        timer: 2500,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        showClass: {
                            popup: 'swal2-noanimation',
                            backdrop: 'swal2-noanimation'
                        },
                    });
                }
                e.clearSelection();
            });
        });
    </script>
@endpush
