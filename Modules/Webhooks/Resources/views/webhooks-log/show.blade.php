@extends('layouts.app')

@push('styles')
    <style>
        .webhook-log-detail .card {
            border: 1px solid #e9ecef !important;
            box-shadow: 0 1px 3px rgba(0,0,0,.08) !important;
        }
        .webhook-log-detail .card-body {
            background: #f8f9fa;
        }
        .webhook-log-detail table {
            background: #fff;
            border: 1px solid #dee2e6;
        }
        .webhook-log-detail table td {
            border-color: #e9ecef;
            padding: 10px 12px;
        }
        .webhook-log-detail table tbody tr:nth-child(odd) {
            background: #fff;
        }
        .webhook-log-detail table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        .webhook-log-detail table tbody tr:hover {
            background: #e9ecef;
        }
        .webhook-log-detail table td:first-child {
            font-weight: 600;
            color: #495057;
            width: 180px;
        }
        .webhook-log-detail pre {
            background: #2d3748;
            color: #e2e8f0;
            padding: 16px;
            border-radius: 6px;
            border: 1px solid #4a5568;
            font-size: 13px;
            line-height: 1.5;
            overflow-x: auto;
        }
    </style>
@endpush

@section('content')
    <div class="content-wrapper border-top-0 client-detail-wrapper webhook-log-detail">
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
            <div class="col-12 mb-4">
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
            <div class="col-12 mb-4">
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
            <div class="col-12 mb-4">
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
@endsection

@push('scripts')
<script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>
<script>
    document.querySelectorAll('.btn-copy-webhook-log').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var targetId = this.getAttribute('data-copy-target');
            var target = document.querySelector(targetId);
            if (target) {
                var text = target.innerText || target.textContent;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(function() {
                        window.Swal.fire({ icon: 'success', title: '@lang("app.copy")', text: '@lang("messages.copiedToClipboard")', timer: 1500, showConfirmButton: false });
                    });
                } else {
                    var textarea = document.createElement('textarea');
                    textarea.value = text;
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                    window.Swal.fire({ icon: 'success', title: '@lang("app.copy")', text: '@lang("messages.copiedToClipboard")', timer: 1500, showConfirmButton: false });
                }
            }
        });
    });
</script>
@endpush
