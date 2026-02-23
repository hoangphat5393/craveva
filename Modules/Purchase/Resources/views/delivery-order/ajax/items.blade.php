<div class="table-responsive">
    <table class="table table-bordered table-hover" id="items-table">
        <thead>
            <tr>
                <th>@lang('app.product')</th>
                <th>@lang('purchase::modules.reports.quantityOrdered')</th>
                <th>@lang('purchase::modules.deliveryOrder.received')</th>
            </tr>
        </thead>
        <tbody>
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
                        <input type="number" class="form-control height-35 f-14" name="quantity_received[]" value="{{ $item->quantity }}" min="0" max="{{ $item->quantity }}">
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
