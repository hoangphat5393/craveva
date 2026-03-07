<div class="row webhook-log-detail">
    <div class="col-sm-12">
        <div class="add-client bg-white rounded">
            <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                @lang('webhooks::app.webhookLog')
            </h4>
            <div class="p-20">
                <div class="row mb-4">
                    <div class="col-12">
                    <x-cards.data>
                        <x-slot:title>
                            @lang('webhooks::app.requestDetails')
                        </x-slot:title>
                        <x-slot:action>
                            <button type="button" class="btn btn-secondary btn-sm btn-copy-webhook-log" data-copy-target="#webhook-log-request-details" title="@lang('app.copy')">
                                <i class="fa fa-copy"></i> @lang('app.copy')
                            </button>
                        </x-slot:action>
                        <div id="webhook-log-request-details">
                        <table class="table table-striped table-hover mb-0">
                            <tbody>
                                <tr>
                                    <td>@lang('webhooks::app.requestUrl')</td>
                                    <td>{{ $log->action }}</td>
                                </tr>
                                <tr>
                                    <td>@lang('webhooks::app.requestMethod')</td>
                                    <td>{{ $log->method }}</td>
                                </tr>
                                <tr>
                                    <td>@lang('app.date')</td>
                                    <td>{{ $log->created_at->timezone($company->timezone)->translatedFormat($company->date_format . ' ' . $company->time_format) }}</td>
                                </tr>
                                <tr>
                                    <td>@lang('webhooks::app.webhookFor')</td>
                                    <td>{{ $log->webhookSettings?->webhook_for }}</td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </x-cards.data>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                    <x-cards.data>
                        <x-slot:title>
                            @lang('webhooks::app.requestHeaders')
                        </x-slot:title>
                        <x-slot:action>
                            <button type="button" class="btn btn-secondary btn-sm btn-copy-webhook-log" data-copy-target="#webhook-log-request-headers" title="@lang('app.copy')">
                                <i class="fa fa-copy"></i> @lang('app.copy')
                            </button>
                        </x-slot:action>
                        <div id="webhook-log-request-headers">
                        <table class="table table-striped table-hover mb-0">
                            <tbody>
                                @forelse (json_decode($log->headers) as $key => $value)
                                    <tr>
                                        <td>{{ $key }}</td>
                                        <td>{{ is_array($value) ? implode(', ', $value) : $value }}</td>
                                    </tr>
                                @empty
                                    <x-cards.no-record-found-list colspan="2" />
                                @endforelse
                            </tbody>
                        </table>
                        </div>
                    </x-cards.data>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                    <x-cards.data>
                        <x-slot:title>
                            @lang('webhooks::app.requestBody')
                        </x-slot:title>
                        <x-slot:action>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-secondary btn-sm btn-copy-webhook-log" data-copy-target="#webhook-log-request-body" title="@lang('app.copy')">
                                    <i class="fa fa-copy"></i> @lang('app.copy')
                                </button>
                                <span class="badge badge-info">@lang('webhooks::app.requestFormat'): {{ $log->webhookSettings?->request_format }}</span>
                            </div>
                        </x-slot:action>
                        <pre id="webhook-log-request-body">{!! $log->raw_content !!}</pre>
                    </x-cards.data>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                    <x-cards.data>
                        <x-slot:title>
                            @lang('webhooks::app.response')
                        </x-slot:title>
                        <x-slot:action>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-secondary btn-sm btn-copy-webhook-log" data-copy-target="#webhook-log-response" title="@lang('app.copy')">
                                    <i class="fa fa-copy"></i> @lang('app.copy')
                                </button>
                                <span class="badge badge-info">{{ $log->response_code }}</span>
                            </div>
                        </x-slot:action>
                        <pre id="webhook-log-response">{{ $log->response }}</pre>
                    </x-cards.data>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
