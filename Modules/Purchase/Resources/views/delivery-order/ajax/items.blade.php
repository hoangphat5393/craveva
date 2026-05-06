@php
    $useDeliveryLines = isset($deliveryItems) && $deliveryItems !== null && $deliveryItems->count() > 0;
@endphp
<div class="table-responsive">
    <table class="table table-bordered table-hover" id="items-table">
        <thead>
            <tr>
                <th>@lang('app.product')</th>
                <th>@lang('purchase::modules.reports.quantityOrdered')</th>
                <th>@lang('purchase::modules.deliveryOrder.quantityReceived')</th>
                <th>@lang('purchase::modules.deliveryOrder.batchLot')</th>
                <th>@lang('purchase::modules.deliveryOrder.expiryDate')</th>
                <th>@lang('purchase::modules.deliveryOrder.pickingRule')</th>
                <th>@lang('purchase::modules.deliveryOrder.qcStatus')</th>
            </tr>
        </thead>
        <tbody>
            @if ($useDeliveryLines)
                @foreach ($deliveryItems as $line)
                    @php
                        $pi = $line->purchaseItem;
                    @endphp
                    <tr>
                        <td>
                            {{ $pi?->item_name ?? '—' }}
                            <input type="hidden" name="item_id[]" value="{{ $line->purchase_item_id }}">
                            <input type="hidden" name="product_id[]" value="{{ $line->product_id }}">
                        </td>
                        <td>
                            {{ $line->quantity_ordered }}
                            <input type="hidden" name="quantity_ordered[]" value="{{ $line->quantity_ordered }}">
                        </td>
                        <td>
                            <input type="number" step="0.0001" class="form-control height-35 f-14" name="quantity_received[]" value="{{ $line->quantity_received }}" min="0">
                        </td>
                        <td>
                            <input type="text" class="form-control height-35 f-14" name="batch_number[]" value="{{ $line->batch_number }}" placeholder="—">
                        </td>
                        <td>
                            <input type="date" class="form-control height-35 f-14" name="expiry_date[]" autocomplete="off" value="{{ $line->expiry_date ? \Carbon\Carbon::parse($line->expiry_date)->format('Y-m-d') : '' }}">
                        </td>
                        <td>
                            <select class="form-control select-picker height-35 f-14" name="picking_rule_applied[]" data-size="3" data-container="body">
                                <option value="">—</option>
                                <option value="FIFO" @selected($line->picking_rule_applied === 'FIFO')>FIFO</option>
                                <option value="FEFO" @selected($line->picking_rule_applied === 'FEFO')>FEFO</option>
                            </select>
                        </td>
                        <td>
                            <select class="form-control select-picker height-35 f-14" name="qc_status[]" data-size="3" data-container="body">
                                <option value="accepted" @selected(($line->qc_status ?? 'accepted') === 'accepted')>@lang('purchase::modules.deliveryOrder.qcAccepted')</option>
                                <option value="pending" @selected(($line->qc_status ?? '') === 'pending')>@lang('purchase::modules.deliveryOrder.qcPending')</option>
                                <option value="rejected" @selected(($line->qc_status ?? '') === 'rejected')>@lang('purchase::modules.deliveryOrder.qcRejected')</option>
                            </select>
                        </td>
                    </tr>
                @endforeach
            @else
                @foreach ($items as $item)
                    <tr>
                        <td>
                            {{ $item->item_name }}
                            <input type="hidden" name="item_id[]" value="{{ $item->id }}">
                            <input type="hidden" name="product_id[]" value="{{ $item->product_id }}">
                        </td>
                        <td>
                            {{ $item->quantity }}
                            <input type="hidden" name="quantity_ordered[]" value="{{ $item->quantity }}">
                        </td>
                        <td>
                            <input type="number" step="0.0001" class="form-control height-35 f-14" name="quantity_received[]" value="{{ $item->quantity }}" min="0" max="{{ $item->quantity }}">
                        </td>
                        <td>
                            <input type="text" class="form-control height-35 f-14" name="batch_number[]" value="" placeholder="—">
                        </td>
                        <td>
                            <input type="date" class="form-control height-35 f-14" name="expiry_date[]" value="" autocomplete="off">
                        </td>
                        <td>
                            <select class="form-control select-picker height-35 f-14" name="picking_rule_applied[]" data-size="3" data-container="body">
                                <option value="">—</option>
                                <option value="FIFO">FIFO</option>
                                <option value="FEFO">FEFO</option>
                            </select>
                        </td>
                        <td>
                            <select class="form-control select-picker height-35 f-14" name="qc_status[]" data-size="3" data-container="body">
                                <option value="accepted">@lang('purchase::modules.deliveryOrder.qcAccepted')</option>
                                <option value="pending">@lang('purchase::modules.deliveryOrder.qcPending')</option>
                                <option value="rejected">@lang('purchase::modules.deliveryOrder.qcRejected')</option>
                            </select>
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>
