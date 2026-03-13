<div class="client-import-log-detail">
    <style>
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
    <div class="card border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">@lang('app.clientImportLogRequestBody')</h5>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-secondary btn-sm btn-copy-import-log" data-copy-target="#client-import-log-body-{{ $log['batch_id'] ?? 'modal' }}" title="@lang('app.copy')">
                    <i class="fa fa-copy"></i> @lang('app.copy')
                </button>
                <span class="badge badge-info">@lang('app.clientImportLogRequestFormat'): json</span>
            </div>
        </div>
        <div class="card-body">
            <pre id="client-import-log-body-{{ $log['batch_id'] ?? 'modal' }}">{{ $logJson }}</pre>
        </div>
    </div>
</div>
