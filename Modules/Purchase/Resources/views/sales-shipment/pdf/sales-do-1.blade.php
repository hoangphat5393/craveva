@php($salesDoLabelKey = config('purchase.flow_naming_mode', 'compat_v2') === 'legacy' ? 'purchase::app.menu.salesShipments' : 'purchase::app.menu.saleDeliveryOrder')
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>@lang($salesDoLabelKey)</title>
    @includeIf('invoices.pdf.invoice_pdf_css')
    <style>
        body {
            font-size: 13px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .items td,
        .items th {
            border: 1px solid #ddd;
            padding: 6px;
        }

        .items th {
            background: #f5f5f5;
            text-align: left;
        }
    </style>
</head>

<body>
    <h2>{{ $company->company_name }}</h2>
    <p><strong>@lang($salesDoLabelKey):</strong> {{ $shipment->shipment_number }}</p>
    <p><strong>@lang('app.order'):</strong> {{ $shipment->order?->order_number ?: '#' . $shipment->order_id }}</p>
    <p><strong>@lang('app.date'):</strong> {{ $shipment->shipment_date?->translatedFormat($invoiceSetting->date_format ?? company()->date_format) }}</p>
    @if ($shipment->warehouse)
        <p><strong>@lang('purchase::modules.deliveryOrder.warehouse'):</strong> {{ $shipment->warehouse->name }}</p>
    @endif

    <table class="items" style="margin-top: 16px;">
        <thead>
            <tr>
                <th>@lang('app.description')</th>
                <th align="right">@lang('purchase::app.shipQty')</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($shipment->items as $item)
                <tr>
                    <td>{{ $item->orderItem?->item_name ?: '#' . $item->order_item_id }}</td>
                    <td align="right">{{ number_format((float) $item->quantity_shipped, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        @if (filled($shipment->notes))
            <p><strong>@lang('app.note')</strong><br>{!! nl2br(e($shipment->notes)) !!}</p>
        @endif
        @include('partials.company-document-terms-pdf', ['invoiceSetting' => $invoiceSetting])
    </div>
</body>

</html>
