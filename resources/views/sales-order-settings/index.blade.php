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

                @if (session('success'))
                    <x-alert type="success" icon="check-circle" class="mb-3">
                        {{ session('success') }}
                    </x-alert>
                @endif

                @if (! $aiOrderWebhookSecretConfigured)
                    <x-alert type="danger" icon="exclamation-circle" class="mb-3">
                        @lang('modules.orders.apiNoWebhookSecret')
                    </x-alert>
                @elseif ($aiOrderGlobalSecretConfigured)
                    <x-alert type="info" icon="info-circle" class="mb-3">
                        @lang('modules.orders.apiGlobalSecretIgnoredForRestHint')
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
                </div>

                <div class="row">
                    @if (!empty($aiOrderRestOrdersUrl))
                        <div class="col-md-12 mb-3">
                            <div class="form-group my-3">
                                <x-forms.label fieldId="ai_rest_orders_url" :fieldLabel="__('modules.orders.apiRestUrlLabel')" fieldRequired="false" />
                                <div class="input-group">
                                    <input type="text" class="form-control height-35 f-14" id="ai_rest_orders_url" name="ai_rest_orders_url" value="{{ $aiOrderRestOrdersUrl }}" readonly>
                                    <div class="input-group-append">
                                        <a href="javascript:;" class="btn btn-secondary btn-copy height-35 f-12 d-flex align-items-center px-3 border-left-0" data-clipboard-target="#ai_rest_orders_url" role="button">
                                            <i class="fa fa-copy mr-1"></i>@lang('app.copy')
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <p class="f-12 text-lightest mt-1">@lang('modules.orders.apiRestUrlHelp')</p>

                            <h6 class="f-14 font-weight-bold text-dark mt-4 mb-2">@lang('modules.orders.apiRestMethodsTitle')</h6>
                            <p class="f-13 text-dark-grey mb-2">@lang('modules.orders.apiRestMethodsIntro')</p>
                            <p class="f-12 text-lightest mb-2">@lang('modules.orders.apiRestMethodPanelsHint')</p>
                            <p class="f-12 text-dark-grey border-left border-primary pl-2 mb-3">@lang('modules.orders.apiRestPostmanManualNote')</p>
                            <div class="mb-3" id="ai-rest-method-panels">
                                <div class="card border border-grey rounded mb-2">
                                    <div class="card-header bg-additional-grey p-0 border-bottom-0" id="headingRestPost">
                                        <button class="btn btn-link text-dark f-14 font-weight-bold w-100 text-left d-flex justify-content-between align-items-center px-3 py-2 text-decoration-none" type="button" data-toggle="collapse" data-target="#collapseRestPost" aria-expanded="true" aria-controls="collapseRestPost">
                                            <span>POST — @lang('modules.orders.apiRestMethodPostRow')</span>
                                            <i class="fa fa-angle-down" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <div id="collapseRestPost" class="collapse show" aria-labelledby="headingRestPost">
                                        <div class="card-body p-3 f-13">
                                            <p class="f-12 text-dark-grey mb-2 mb-md-3">@lang('modules.orders.apiRestMethodsTableUrl')</p>
                                            <div class="mb-3">
                                                <input type="text" class="form-control height-35 f-12 mb-2" id="ai_rest_copy_post" value="{{ $aiOrderRestOrdersUrl }}" readonly>
                                                <a href="javascript:;" class="btn btn-secondary btn-sm btn-copy f-12" data-clipboard-target="#ai_rest_copy_post" role="button">@lang('modules.orders.apiRestCopyUrl')</a>
                                            </div>
                                            <p class="f-12 text-dark-grey mb-2">@lang('modules.orders.apiRestMethodsTablePostmanExample')</p>
                                            <textarea class="form-control f-12 font-weight-normal mb-2" id="ai_rest_tpl_post" rows="14" readonly style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">{{ $aiOrderRestPostmanExamplePost }}</textarea>
                                            <a href="javascript:;" class="btn btn-secondary btn-sm btn-copy f-12 mb-3" data-clipboard-target="#ai_rest_tpl_post" role="button">@lang('modules.orders.apiRestCopyTemplate')</a>
                                            <p class="f-12 text-dark-grey mb-2">@lang('modules.orders.apiRestMethodsTableCurlExample')</p>
                                            <textarea class="form-control f-12 font-weight-normal mb-2" id="ai_rest_curl_post" rows="10" readonly style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">{{ $aiOrderRestCurlExamplePost }}</textarea>
                                            <a href="javascript:;" class="btn btn-secondary btn-sm btn-copy f-12" data-clipboard-target="#ai_rest_curl_post" role="button">@lang('modules.orders.apiRestCopyCurl')</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card border border-grey rounded mb-2">
                                    <div class="card-header bg-additional-grey p-0 border-bottom-0" id="headingRestGet">
                                        <button class="btn btn-link text-dark f-14 font-weight-bold w-100 text-left d-flex justify-content-between align-items-center px-3 py-2 text-decoration-none collapsed" type="button" data-toggle="collapse" data-target="#collapseRestGet" aria-expanded="false" aria-controls="collapseRestGet">
                                            <span>GET — @lang('modules.orders.apiRestMethodGetRow')</span>
                                            <i class="fa fa-angle-down" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <div id="collapseRestGet" class="collapse" aria-labelledby="headingRestGet">
                                        <div class="card-body p-3 f-13">
                                            <p class="f-12 text-dark-grey mb-2 mb-md-3">@lang('modules.orders.apiRestMethodsTableUrl')</p>
                                            <div class="mb-3">
                                                <input type="text" class="form-control height-35 f-12 mb-2" id="ai_rest_copy_get" value="{{ $aiOrderRestOrdersUrl }}/YOUR_ORDER_ID" readonly>
                                                <a href="javascript:;" class="btn btn-secondary btn-sm btn-copy f-12" data-clipboard-target="#ai_rest_copy_get" role="button">@lang('modules.orders.apiRestCopyUrl')</a>
                                            </div>
                                            <p class="f-12 text-dark-grey mb-2">@lang('modules.orders.apiRestMethodsTablePostmanExample')</p>
                                            <textarea class="form-control f-12 font-weight-normal mb-2" id="ai_rest_tpl_get" rows="8" readonly style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">{{ $aiOrderRestPostmanExampleGet }}</textarea>
                                            <a href="javascript:;" class="btn btn-secondary btn-sm btn-copy f-12 mb-3" data-clipboard-target="#ai_rest_tpl_get" role="button">@lang('modules.orders.apiRestCopyTemplate')</a>
                                            <p class="f-12 text-dark-grey mb-2">@lang('modules.orders.apiRestMethodsTableCurlExample')</p>
                                            <textarea class="form-control f-12 font-weight-normal mb-2" id="ai_rest_curl_get" rows="5" readonly style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">{{ $aiOrderRestCurlExampleGet }}</textarea>
                                            <a href="javascript:;" class="btn btn-secondary btn-sm btn-copy f-12" data-clipboard-target="#ai_rest_curl_get" role="button">@lang('modules.orders.apiRestCopyCurl')</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card border border-grey rounded mb-2">
                                    <div class="card-header bg-additional-grey p-0 border-bottom-0" id="headingRestPatch">
                                        <button class="btn btn-link text-dark f-14 font-weight-bold w-100 text-left d-flex justify-content-between align-items-center px-3 py-2 text-decoration-none collapsed" type="button" data-toggle="collapse" data-target="#collapseRestPatch" aria-expanded="false" aria-controls="collapseRestPatch">
                                            <span>PATCH — @lang('modules.orders.apiRestMethodPatchRow')</span>
                                            <i class="fa fa-angle-down" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <div id="collapseRestPatch" class="collapse" aria-labelledby="headingRestPatch">
                                        <div class="card-body p-3 f-13">
                                            <p class="f-12 text-dark-grey mb-2 mb-md-3">@lang('modules.orders.apiRestMethodsTableUrl')</p>
                                            <div class="mb-3">
                                                <input type="text" class="form-control height-35 f-12 mb-2" id="ai_rest_copy_patch" value="{{ $aiOrderRestOrdersUrl }}/YOUR_ORDER_ID" readonly>
                                                <a href="javascript:;" class="btn btn-secondary btn-sm btn-copy f-12" data-clipboard-target="#ai_rest_copy_patch" role="button">@lang('modules.orders.apiRestCopyUrl')</a>
                                            </div>
                                            <p class="f-12 text-dark-grey mb-2">@lang('modules.orders.apiRestMethodsTablePostmanExample')</p>
                                            <textarea class="form-control f-12 font-weight-normal mb-2" id="ai_rest_tpl_patch" rows="12" readonly style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">{{ $aiOrderRestPostmanExamplePatch }}</textarea>
                                            <a href="javascript:;" class="btn btn-secondary btn-sm btn-copy f-12 mb-3" data-clipboard-target="#ai_rest_tpl_patch" role="button">@lang('modules.orders.apiRestCopyTemplate')</a>
                                            <p class="f-12 text-dark-grey mb-2">@lang('modules.orders.apiRestMethodsTableCurlExample')</p>
                                            <textarea class="form-control f-12 font-weight-normal mb-2" id="ai_rest_curl_patch" rows="8" readonly style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">{{ $aiOrderRestCurlExamplePatch }}</textarea>
                                            <a href="javascript:;" class="btn btn-secondary btn-sm btn-copy f-12" data-clipboard-target="#ai_rest_curl_patch" role="button">@lang('modules.orders.apiRestCopyCurl')</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card border border-grey rounded mb-2">
                                    <div class="card-header bg-additional-grey p-0 border-bottom-0" id="headingRestPut">
                                        <button class="btn btn-link text-dark f-14 font-weight-bold w-100 text-left d-flex justify-content-between align-items-center px-3 py-2 text-decoration-none collapsed" type="button" data-toggle="collapse" data-target="#collapseRestPut" aria-expanded="false" aria-controls="collapseRestPut">
                                            <span>PUT — @lang('modules.orders.apiRestMethodPutRow')</span>
                                            <i class="fa fa-angle-down" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <div id="collapseRestPut" class="collapse" aria-labelledby="headingRestPut">
                                        <div class="card-body p-3 f-13">
                                            <p class="f-12 text-dark-grey mb-2 mb-md-3">@lang('modules.orders.apiRestMethodsTableUrl')</p>
                                            <div class="mb-3">
                                                <input type="text" class="form-control height-35 f-12 mb-2" id="ai_rest_copy_put" value="{{ $aiOrderRestOrdersUrl }}/YOUR_ORDER_ID" readonly>
                                                <a href="javascript:;" class="btn btn-secondary btn-sm btn-copy f-12" data-clipboard-target="#ai_rest_copy_put" role="button">@lang('modules.orders.apiRestCopyUrl')</a>
                                            </div>
                                            <p class="f-12 text-dark-grey mb-2">@lang('modules.orders.apiRestMethodsTablePostmanExample')</p>
                                            <textarea class="form-control f-12 font-weight-normal mb-2" id="ai_rest_tpl_put" rows="12" readonly style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">{{ $aiOrderRestPostmanExamplePut }}</textarea>
                                            <a href="javascript:;" class="btn btn-secondary btn-sm btn-copy f-12 mb-3" data-clipboard-target="#ai_rest_tpl_put" role="button">@lang('modules.orders.apiRestCopyTemplate')</a>
                                            <p class="f-12 text-dark-grey mb-2">@lang('modules.orders.apiRestMethodsTableCurlExample')</p>
                                            <textarea class="form-control f-12 font-weight-normal mb-2" id="ai_rest_curl_put" rows="8" readonly style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">{{ $aiOrderRestCurlExamplePut }}</textarea>
                                            <a href="javascript:;" class="btn btn-secondary btn-sm btn-copy f-12" data-clipboard-target="#ai_rest_curl_put" role="button">@lang('modules.orders.apiRestCopyCurl')</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card border border-grey rounded mb-0">
                                    <div class="card-header bg-additional-grey p-0 border-bottom-0" id="headingRestDelete">
                                        <button class="btn btn-link text-dark f-14 font-weight-bold w-100 text-left d-flex justify-content-between align-items-center px-3 py-2 text-decoration-none collapsed" type="button" data-toggle="collapse" data-target="#collapseRestDelete" aria-expanded="false" aria-controls="collapseRestDelete">
                                            <span>DELETE — @lang('modules.orders.apiRestMethodDeleteRow')</span>
                                            <i class="fa fa-angle-down" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <div id="collapseRestDelete" class="collapse" aria-labelledby="headingRestDelete">
                                        <div class="card-body p-3 f-13">
                                            <p class="f-12 text-dark-grey mb-2 mb-md-3">@lang('modules.orders.apiRestMethodsTableUrl')</p>
                                            <div class="mb-3">
                                                <input type="text" class="form-control height-35 f-12 mb-2" id="ai_rest_copy_delete" value="{{ $aiOrderRestOrdersUrl }}/YOUR_ORDER_ID" readonly>
                                                <a href="javascript:;" class="btn btn-secondary btn-sm btn-copy f-12" data-clipboard-target="#ai_rest_copy_delete" role="button">@lang('modules.orders.apiRestCopyUrl')</a>
                                            </div>
                                            <p class="f-12 text-dark-grey mb-2">@lang('modules.orders.apiRestMethodsTablePostmanExample')</p>
                                            <textarea class="form-control f-12 font-weight-normal mb-2" id="ai_rest_tpl_delete" rows="8" readonly style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">{{ $aiOrderRestPostmanExampleDelete }}</textarea>
                                            <a href="javascript:;" class="btn btn-secondary btn-sm btn-copy f-12 mb-3" data-clipboard-target="#ai_rest_tpl_delete" role="button">@lang('modules.orders.apiRestCopyTemplate')</a>
                                            <p class="f-12 text-dark-grey mb-2">@lang('modules.orders.apiRestMethodsTableCurlExample')</p>
                                            <textarea class="form-control f-12 font-weight-normal mb-2" id="ai_rest_curl_delete" rows="5" readonly style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">{{ $aiOrderRestCurlExampleDelete }}</textarea>
                                            <a href="javascript:;" class="btn btn-secondary btn-sm btn-copy f-12" data-clipboard-target="#ai_rest_curl_delete" role="button">@lang('modules.orders.apiRestCopyCurl')</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="col-md-12 mb-4">
                        <h6 class="f-14 font-weight-bold text-dark mb-2">@lang('modules.orders.apiCrudPermissionsTitle')</h6>
                        <p class="f-13 text-dark-grey mb-3">@lang('modules.orders.apiCrudPermissionsIntro')</p>
                        <form method="post" action="{{ route('sales-order-settings.update-integration-permissions') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <input type="hidden" name="ai_order_integration_allow_create" value="0" />
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="ai_order_integration_allow_create" name="ai_order_integration_allow_create" value="1" @checked($aiOrderIntegrationAllowCreate) />
                                        <label class="custom-control-label f-13" for="ai_order_integration_allow_create">@lang('modules.orders.apiCrudCreate')</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <input type="hidden" name="ai_order_integration_allow_read" value="0" />
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="ai_order_integration_allow_read" name="ai_order_integration_allow_read" value="1" @checked($aiOrderIntegrationAllowRead) />
                                        <label class="custom-control-label f-13" for="ai_order_integration_allow_read">@lang('modules.orders.apiCrudRead')</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <input type="hidden" name="ai_order_integration_allow_update" value="0" />
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="ai_order_integration_allow_update" name="ai_order_integration_allow_update" value="1" @checked($aiOrderIntegrationAllowUpdate) />
                                        <label class="custom-control-label f-13" for="ai_order_integration_allow_update">@lang('modules.orders.apiCrudUpdate')</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <input type="hidden" name="ai_order_integration_allow_delete" value="0" />
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="ai_order_integration_allow_delete" name="ai_order_integration_allow_delete" value="1" @checked($aiOrderIntegrationAllowDelete) />
                                        <label class="custom-control-label f-13" for="ai_order_integration_allow_delete">@lang('modules.orders.apiCrudDelete')</label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary rounded f-14 p-2 mt-2">@lang('modules.orders.apiCrudSave')</button>
                        </form>
                    </div>
                </div>

                <div class="mb-4">
                    <p class="f-13 text-dark-grey mb-0">@lang('modules.orders.apiIntegrationRestOnlyFooter')</p>
                </div>
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
