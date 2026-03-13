@extends('layouts.app')

@push('styles')
    <style>
        .client-import-log-detail .card {
            border: 1px solid #e9ecef !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .08) !important;
        }

        .client-import-log-detail .card-body {
            background: #f8f9fa;
        }

        .client-import-log-detail pre {
            background: #2d3748 !important;
            color: #e2e8f0 !important;
            padding: 16px !important;
            border-radius: 6px !important;
            border: 1px solid #4a5568 !important;
            font-size: 13px !important;
            line-height: 1.5 !important;
            overflow-x: auto !important;
        }
    </style>
@endpush

@section('content')
    <div class="content-wrapper border-top-0 client-detail-wrapper client-import-log-detail">
        <div class="d-flex justify-content-between action-bar mb-3">
            <x-forms.link-secondary :link="route('clients.import_log.index')" icon="arrow-left">
                @lang('app.back')
            </x-forms.link-secondary>
        </div>
        <div class="row mb-4">
            <div class="col-12">
                <x-cards.data>
                    <x-slot:title>
                        @lang('app.clientImportLogRequestBody')
                    </x-slot:title>
                    <x-slot:action>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-secondary btn-sm btn-copy-import-log" data-copy-target="#client-import-log-body" title="@lang('app.copy')">
                                <i class="fa fa-copy"></i> @lang('app.copy')
                            </button>
                            <span class="badge badge-info">@lang('app.clientImportLogRequestFormat'): json</span>
                        </div>
                    </x-slot:action>
                    <pre id="client-import-log-body">{{ $logJson }}</pre>
                </x-cards.data>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.btn-copy-import-log').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var targetId = this.getAttribute('data-copy-target');
                var target = document.querySelector(targetId);
                if (target) {
                    var text = target.innerText || target.textContent;
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(text).then(function() {
                            window.Swal.fire({
                                icon: 'success',
                                title: '@lang('app.copy')',
                                text: '@lang('messages.copiedToClipboard')',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        });
                    } else {
                        var textarea = document.createElement('textarea');
                        textarea.value = text;
                        document.body.appendChild(textarea);
                        textarea.select();
                        document.execCommand('copy');
                        document.body.removeChild(textarea);
                        window.Swal.fire({
                            icon: 'success',
                            title: '@lang('app.copy')',
                            text: '@lang('messages.copiedToClipboard')',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                }
            });
        });
    </script>
@endpush
