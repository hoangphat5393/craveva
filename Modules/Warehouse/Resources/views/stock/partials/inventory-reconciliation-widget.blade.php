@php
    $fmt = static fn($v): string => rtrim(rtrim(number_format((float) $v, 4, '.', ''), '0'), '.');
    $mismatchCount = (int) ($widget['mismatch_count'] ?? 0);
    $significantCount = (int) ($widget['significant_mismatch_count'] ?? 0);
    $samples = $widget['samples'] ?? [];
@endphp
@if ($mismatchCount > 0)
    @php
        $isMaterial = $significantCount > 0;
        $alertClass = $isMaterial ? 'alert-warning border-warning' : 'alert-info border-info';
        $badgeClass = $isMaterial ? 'badge-warning text-dark' : 'badge-info';
    @endphp
    <div class="alert {{ $alertClass }} mt-3 mb-0" role="alert">
        <div class="d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <strong>{{ $isMaterial ? __('warehouse::app.batchSnapshotMismatchTitle') : __('warehouse::app.batchSnapshotMinorMismatchTitle') }}</strong>
                <span class="f-13 d-block mt-1 text-dark-grey">{{ $isMaterial ? __('warehouse::app.batchSnapshotMismatchHelp') : __('warehouse::app.batchSnapshotMinorMismatchHelp') }}</span>
            </div>
            <div class="text-right mt-1">
                <span class="badge {{ $badgeClass }}">{{ $mismatchCount }} @lang('warehouse::app.batchSnapshotMismatchRows')</span>
                @if ($significantCount > 0 && $significantCount < $mismatchCount)
                    <span class="badge badge-light border ml-1">{{ $significantCount }} @lang('warehouse::app.batchSnapshotMaterialRows')</span>
                @endif
            </div>
        </div>
        @if (count($samples) > 0)
            <div class="table-responsive mt-3 mb-0">
                <table class="table table-sm table-bordered bg-white mb-0 f-13">
                    <thead>
                        <tr>
                            <th>@lang('warehouse::app.warehouse') ID</th>
                            <th>@lang('warehouse::app.product') ID</th>
                            <th>@lang('warehouse::app.batchSnapshotColSnapshot')</th>
                            <th>@lang('warehouse::app.batchSnapshotColBatches')</th>
                            <th>@lang('warehouse::app.batchSnapshotColDelta')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($samples as $row)
                            @php
                                $significantRow = (bool) ($row['is_significant'] ?? false);
                                $deltaClass = $significantRow ? 'font-weight-semibold text-danger' : 'text-muted';
                            @endphp
                            <tr class="{{ $significantRow ? 'table-warning' : '' }}">
                                <td>{{ $row['warehouse_id'] }}</td>
                                <td>
                                    {{ $row['product_id'] }}
                                    @if (!empty($row['product_name']))
                                        <span class="text-muted"> — {{ $row['product_name'] }}</span>
                                    @endif
                                </td>
                                <td>{{ $fmt($row['snapshot_quantity'] ?? 0) }}</td>
                                <td>{{ $fmt($row['batches_quantity'] ?? 0) }}</td>
                                <td class="{{ $deltaClass }}">{{ $fmt($row['delta'] ?? 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@else
    <div class="alert alert-success border-0 mt-3 mb-0 f-13" role="status">
        <i class="fa fa-check-circle mr-1"></i>@lang('warehouse::app.batchSnapshotInSync')
    </div>
@endif
