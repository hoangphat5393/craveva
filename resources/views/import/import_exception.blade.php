<div class="bg-white rounded p-3 border" id="import-log-section">
    <h5 class="mb-3">@lang('app.importLog')</h5>

    @if (isset($summary) && $summary)
        <div class="alert {{ ($summary['failed_jobs'] ?? 0) > 0 ? 'alert-warning' : 'alert-success' }} mb-3">
            @lang('app.importSummary', [
                'processed' => $summary['processed_jobs'] ?? 0,
                'failed' => $summary['failed_jobs'] ?? 0,
                'total' => $summary['total_jobs'] ?? 0,
            ])
        </div>
    @endif

    @if (!empty($importRowErrors))
        <p class="text-muted small mb-2">@lang('app.importRowErrorsTitle')</p>
        <p class="f-12 text-dark-grey mb-2">@lang('app.importRowErrorsHelp')</p>
        <div class="d-flex flex-wrap mb-3" style="gap: 0.5rem;">
            @foreach ($importRowErrors as $rowErr)
                <span class="badge badge-danger" style="white-space: normal; text-align: left; font-weight: 500;">{{ $rowErr }}</span>
            @endforeach
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm mb-3" id="import-row-errors-download-csv">@lang('app.downloadImportRowErrorsCsv')</button>
    @endif

    @if (!empty($failedRows))
        <p class="text-muted small mb-2">@lang('app.importLogFailedRows')</p>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm" id="import_failed_rows_table">
                <thead>
                    <tr>
                        <th class="text-right" style="width: 100px;">@lang('app.rowNumber')</th>
                        <th>@lang('app.exceptions')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($failedRows as $item)
                        <tr>
                            <td class="text-right">{{ $item['row'] }}</td>
                            <td style="white-space: pre-wrap;">{{ $item['message'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="import-log-download-csv">@lang('app.downloadFailedRowsCsv')</button>
    @elseif(!empty($exceptions) && empty($failedRows))
        @if (!empty($importRowErrors))
            <p class="text-muted small mb-2">@lang('app.importFailedJobsSummary')</p>
        @endif
        <table class="table table-bordered table-striped table-sm" id="import_table_body">
            <thead>
                <tr>
                    <th>@lang('app.exceptions')</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($exceptions as $exception)
                    <tr>
                        <td style="white-space: pre-line;">{{ $exception->exception }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif(isset($summary) && ($summary['failed_jobs'] ?? 0) == 0 && count($exceptions ?? []) == 0)
        <p class="text-success mb-0">@lang('app.allRowsImportedSuccessfully')</p>
    @else
        {{-- Fallback: raw exception messages (e.g. old format or other job types) --}}
        <table class="table table-bordered table-striped table-sm" id="import_table_body">
            <thead>
                <tr>
                    <th>@lang('app.exceptions')</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($exceptions ?? [] as $exception)
                    <tr>
                        <td style="white-space: pre-line;">{{ $exception->exception }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@if (!empty($importRowErrors))
    <script>
        (function() {
            var btn = document.getElementById('import-row-errors-download-csv');
            if (!btn) return;
            var lines = @json($importRowErrors);
            btn.addEventListener('click', function() {
                var csv = 'Error\n';
                for (var i = 0; i < lines.length; i++) {
                    var msg = String(lines[i]).replace(/"/g, '""');
                    csv += '"' + msg + '"\n';
                }
                var blob = new Blob([csv], {
                    type: 'text/csv;charset=utf-8;'
                });
                var link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'import_row_errors.csv';
                link.click();
                URL.revokeObjectURL(link.href);
            });
        })();
    </script>
@endif

@if (!empty($failedRows))
    <script>
        (function() {
            document.getElementById('import-log-download-csv').addEventListener('click', function() {
                var table = document.getElementById('import_failed_rows_table');
                var rows = table.querySelectorAll('tbody tr');
                var csv = 'Row #,Error\n';
                for (var i = 0; i < rows.length; i++) {
                    var cells = rows[i].querySelectorAll('td');
                    if (cells.length >= 2) {
                        var rowNum = cells[0].textContent.trim();
                        var msg = cells[1].textContent.trim().replace(/"/g, '""');
                        csv += '"' + rowNum + '","' + msg + '"\n';
                    }
                }
                var blob = new Blob([csv], {
                    type: 'text/csv;charset=utf-8;'
                });
                var link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'import_failed_rows.csv';
                link.click();
                URL.revokeObjectURL(link.href);
            });
        })();
    </script>
@endif
