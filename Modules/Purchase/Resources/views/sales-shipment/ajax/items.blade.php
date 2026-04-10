@php
    $existing = isset($shipment) && $shipment ? $shipment->items->keyBy('order_item_id') : collect();
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
                @endphp
                <tr>
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
                        <input type="number" step="0.01" min="0" max="{{ $maxQty }}" class="form-control text-right" name="quantity_shipped[]" value="{{ $lineDisabled ? 0 : $qtyShipped }}" @disabled($lineDisabled)>
                    </td>
                    <td>
                        <input type="text" class="form-control" name="batch_number[]" value="{{ $line?->batch_number }}" @disabled($lineDisabled)>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
