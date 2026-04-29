@php
    $existing = isset($shipment) && $shipment ? $shipment->items->keyBy('order_item_id') : collect();
    $batchOptionsByOrderItem = $batchOptionsByOrderItem ?? [];
@endphp

<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>@lang('app.description')</th>
                <th>@lang('purchase::app.sku')</th>
                <th class="text-right">@lang('modules.invoices.qty')</th>
                <th class="text-right">@lang('purchase::app.remainingQty')</th>
                <th class="text-right">@lang('purchase::app.shipQty')</th>
                <th>@lang('purchase::app.batchNumber')</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                @php
                    $line = $existing->get($item->id);
                    $remaining = (float) ($remainingByItem[$item->id] ?? 0);
                    $qtyShipped = $line ? (float) $line->quantity_shipped : 0;
                    $maxQty = $remaining + $qtyShipped;
                    $lineDisabled = $maxQty <= 0;
                    $lineSku = $item->sku ?: $item->product?->sku ?? null;
                    $batchOptions = collect($batchOptionsByOrderItem[$item->id] ?? []);
                    $lineExpiry = $line?->expiration_date ? \Carbon\Carbon::parse($line->expiration_date)->format('Y-m-d') : null;
                    $selectedBatchId = $line?->warehouse_batch_id;
                    if (!$selectedBatchId && $line?->batch_number) {
                        $matched = $batchOptions->first(function ($opt) use ($line, $lineExpiry) {
                            return ($opt['batch_number'] ?? null) === $line->batch_number && ($opt['expiration_date'] ?? null) === $lineExpiry;
                        });
                        $selectedBatchId = $matched['id'] ?? null;
                    }
                    $missingBatchOptions = $batchOptions->isEmpty();
                    $requiresBatchSelection = $batchOptions->contains(function ($opt) {
                        return filled($opt['batch_number'] ?? null) || filled($opt['expiration_date'] ?? null);
                    });
                @endphp
                <tr data-requires-batch="{{ $requiresBatchSelection ? '1' : '0' }}">
                    <td>
                        {{ $item->item_name }}
                        <input type="hidden" name="order_item_id[]" value="{{ $item->id }}">
                        <input type="hidden" name="product_id[]" value="{{ $item->product_id }}">
                        <input type="hidden" name="unit_id[]" value="{{ $item->unit_id }}">
                        <input type="hidden" name="quantity_ordered[]" value="{{ $item->quantity }}">
                    </td>
                    <td class="text-dark-grey f-12">{{ $lineSku ?: '—' }}</td>
                    <td class="text-right">{{ number_format((float) $item->quantity, 2) }}</td>
                    <td class="text-right">{{ number_format($remaining, 2) }}</td>
                    <td class="text-right">
                        <input type="number" step="0.01" min="0" max="{{ $maxQty }}" class="form-control text-right shipment-qty-input" name="quantity_shipped[]" value="{{ $lineDisabled ? 0 : $qtyShipped }}" @disabled($lineDisabled)>
                    </td>
                    <td>
                        <input type="hidden" name="batch_number[]" value="{{ $line?->batch_number }}" class="shipment-batch-number-input">
                        <input type="hidden" name="expiration_date[]" value="{{ $lineExpiry }}" class="shipment-expiry-date-input">
                        <input type="hidden" name="warehouse_batch_id[]" value="{{ $selectedBatchId ?: '' }}" class="shipment-batch-id-input">
                        <select class="form-control select-picker shipment-batch-select" name="warehouse_batch_ui[]" data-live-search="true" @disabled($lineDisabled)>
                            <option value="">--</option>
                            @foreach ($batchOptions as $batch)
                                @php
                                    $batchIdentity = $batch['batch_number'] ?? null;
                                    if (!filled($batchIdentity)) {
                                        $batchIdentity = 'Batch#' . ($batch['id'] ?? '—');
                                    }
                                    $batchLabel = $batchIdentity . ' | ' . ($batch['expiration_date'] ?? null ?: 'No expiry') . ' | Avl: ' . number_format((float) ($batch['available_quantity'] ?? 0), 4, '.', '');
                                @endphp
                                <option value="{{ $batch['id'] }}" data-batch-number="{{ $batch['batch_number'] }}" data-expiry-date="{{ $batch['expiration_date'] }}" data-available-quantity="{{ (float) ($batch['available_quantity'] ?? 0) }}" @selected((int) $selectedBatchId === (int) $batch['id'])>
                                    {{ $batchLabel }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-danger d-none shipment-batch-error mt-1"></small>
                        @if ($requiresBatchSelection && $missingBatchOptions && !$lineDisabled)
                            <small class="text-warning d-block mt-1">No batch found for selected warehouse. If this item is batch-tracked, create inbound batch first.</small>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    window.syncShipmentBatchIdentity = function(selectEl) {
        const $select = $(selectEl);
        const $row = $select.closest('tr');
        const selectedBatchId = String($select.val() || '');
        const $opt = $select.find('option:selected');
        const batchNumber = $opt.data('batch-number') || '';
        const expiryDate = $opt.data('expiry-date') || '';
        $row.find('.shipment-batch-id-input').val(selectedBatchId);
        $row.find('.shipment-batch-number-input').val(batchNumber);
        $row.find('.shipment-expiry-date-input').val(expiryDate);
    };

    window.syncAllShipmentBatchRows = function() {
        $('.shipment-batch-select').each(function() {
            window.syncShipmentBatchIdentity(this);
        });
    };

    window.validateSalesShipmentRows = function() {
        let firstError = null;
        $('.shipment-batch-error').addClass('d-none').text('');

        $('#sales-shipment-items tbody tr').each(function() {
            const $row = $(this);
            const itemName = $.trim($row.find('td:first').clone().children().remove().end().text()) || 'Item';
            const qty = parseFloat($row.find('.shipment-qty-input').val() || '0');
            const $batchSelect = $row.find('.shipment-batch-select');
            const selectedBatchId = String($batchSelect.val() || '').trim();
            const $selectedOption = $batchSelect.find('option:selected');
            const availableQty = parseFloat($selectedOption.data('available-quantity') || '0');
            const requiresBatch = String($row.attr('data-requires-batch') || '0') === '1';
            const $err = $row.find('.shipment-batch-error');

            if (qty <= 0) {
                return;
            }

            if (requiresBatch && !selectedBatchId) {
                const msg = `Please select batch for "${itemName}" before shipping.`;
                $err.text(msg).removeClass('d-none');
                firstError = firstError || msg;
                return;
            }

            if (requiresBatch && selectedBatchId && Number.isFinite(availableQty) && qty > availableQty) {
                const msg = `Ship qty for "${itemName}" exceeds selected batch available (${availableQty.toFixed(4)}).`;
                $err.text(msg).removeClass('d-none');
                firstError = firstError || msg;
            }
        });

        return firstError;
    };

    window.syncAllShipmentBatchRows();

    $('.shipment-batch-select').on('change', function() {
        window.syncShipmentBatchIdentity(this);
        $(this).closest('tr').find('.shipment-batch-error').addClass('d-none').text('');
    });

    $('.shipment-qty-input').on('input', function() {
        $(this).closest('tr').find('.shipment-batch-error').addClass('d-none').text('');
    });

    if (typeof $.fn.selectpicker !== 'undefined') {
        $('.shipment-batch-select').selectpicker();
        $('.shipment-batch-select').selectpicker('refresh');
    }
</script>
