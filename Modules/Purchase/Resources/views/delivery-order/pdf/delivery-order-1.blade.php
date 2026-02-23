<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>@lang('purchase::app.menu.deliveryOrders')</title>
    @includeIf('invoices.pdf.invoice_pdf_css')
    <style>
        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }

        a {
            text-decoration: none;
        }

        body {
            position: relative;
            width: 100%;
            height: auto;
            margin: 0 auto;
            color: #555555;
            background: #FFFFFF;
            font-size: 13px;
        }

        h2 {
            font-weight: normal;
        }

        header {
            padding: 10px 0;
        }

        #logo img {
            height: 50px;
            margin-bottom: 15px;
        }

        #details {
            margin-bottom: 25px;
        }

        #client {
            padding-left: 6px;
            float: left;
        }

        #client .to {
            color: #777777;
        }

        h2.name {
            font-size: 1.2em;
            font-weight: normal;
            margin: 0;
        }

        #invoice h1 {
            color: #0087C3;
            line-height: 2em;
            font-weight: normal;
            margin: 0 0 10px 0;
            font-size: 20px;
        }

        #invoice .date {
            font-size: 1.1em;
            color: #777777;
        }

        table {
            width: 100%;
            border-spacing: 0;
        }

        table th,
        table td {
            padding: 5px 8px;
            text-align: center;
        }

        table th {
            background: #EEEEEE;
        }

        table th {
            white-space: nowrap;
            font-weight: normal;
        }

        table td {
            text-align: right;
        }

        table td.desc h3,
        table td.qty h3 {
            font-size: 0.9em;
            font-weight: normal;
            margin: 0 0 0 0;
        }

        table .no {
            font-size: 1.2em;
            width: 10%;
            text-align: center;
            border-left: 1px solid #e7e9eb;
        }

        table .desc,
        table .item-summary {
            text-align: left;
        }

        table .unit {
            border: 1px solid #e7e9eb;
        }

        table td.unit,
        table td.qty,
        table td.total {
            font-size: 1.2em;
            text-align: center;
        }

        table td.unit {
            width: 35%;
        }

        table td.desc {
            width: 45%;
        }

        table td.qty {
            width: 5%;
        }

        .status {
            margin-top: 15px;
            padding: 1px 8px 5px;
            font-size: 1.3em;
            width: 80px;
            float: right;
            text-align: center;
            display: inline-block;
        }

        .background-green {
            background-color: #57B223;
            color: #FFFFFF;
        }

        .text-green {
            background-color: #e7e9eb;
            color: #57B223;
        }

        .text-dark-grey {
            background-color: #ced0d2;
        }

        .word-break {
            word-wrap: break-word;
        }

        .border-left-0 {
            border-left: 0 !important;
        }

        .border-right-0 {
            border-right: 0 !important;
        }

        .border-top-0 {
            border-top: 0 !important;
        }

        .border-bottom-0 {
            border-bottom: 0 !important;
        }
    </style>
</head>

<body>
    <header class="clearfix" class="description">

        <table cellpadding="0" cellspacing="0" class="billing">
            <tr>
                <td colspan="2">
                    <h1>@lang('purchase::app.menu.deliveryOrders')</h1>
                </td>
            </tr>
            <tr>
                <td id="ordered_to">
                    <div class="description">
                        <small>@lang('modules.invoices.billedTo'):</small><br>

                        {{ mb_ucwords(company()->company_name) }}<br>
                        @if ($delivery->purchaseOrder && $delivery->purchaseOrder->address)
                            {!! nl2br($delivery->purchaseOrder->address->address) !!}<br>
                        @endif
                        {{ company()->company_phone }}
                    </div>
                </td>
                <td>
                    <div id="company" class="description">
                        <div id="logo">
                            <img src="{{ $invoiceSetting->logo_url }}" alt="home" class="dark-logo" />
                        </div>
                        <small>@lang('modules.invoices.billedFrom'):</small>
                        @if ($delivery->purchaseOrder && $delivery->purchaseOrder->vendor && $delivery->purchaseOrder->vendor->primary_name)
                            {{ $delivery->purchaseOrder->vendor->primary_name }}<br>
                        @endif
                        @if ($delivery->purchaseOrder && $delivery->purchaseOrder->vendor && $delivery->purchaseOrder->vendor->email)
                            {{ $delivery->purchaseOrder->vendor->email }}<br>
                        @endif
                        @if ($delivery->purchaseOrder && $delivery->purchaseOrder->vendor && $delivery->purchaseOrder->vendor->billing_address)
                            {{ $delivery->purchaseOrder->vendor->billing_address }}<br>
                        @endif
                        @if ($delivery->purchaseOrder && $delivery->purchaseOrder->vendor && $delivery->purchaseOrder->vendor->phone)
                            {{ $delivery->purchaseOrder->vendor->phone }}<br>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </header>
    <main>
        <div id="details">
            <div id="order" class="description">
                <h1>{{ $delivery->delivery_number }}</h1>

                <div class="date">@lang('app.date'):
                    {{ \Carbon\Carbon::parse($delivery->delivery_date)->translatedFormat(company()->date_format) }}</div>

                <div class="">@lang('app.status'): {{ $delivery->status }}</div>
                <div class="">@lang('purchase::app.menu.purchaseOrder'): {{ optional($delivery->purchaseOrder)->purchase_order_number }}</div>
            </div>
        </div>
        <table cellspacing="0" cellpadding="0" id="invoice-table">
            <thead>
                <tr style="border-bottom: 1px solid #FFFFFF;">
                    <th class="no description background-green">#</th>
                    <th class="desc description">@lang('modules.invoices.item')</th>
                    <th class="qty description">@lang('modules.invoices.qty')</th>
                </tr>
            </thead>
            <tbody>
                <?php $count = 0; ?>
                @if ($delivery->items->count() > 0)
                    @foreach ($delivery->items as $item)
                        <tr style="page-break-inside: avoid;">
                            <td class="no background-green">{{ ++$count }}</td>
                            <td class="desc text-green">
                                <h3 class="description">{{ $item->purchaseItem->item_name }}</h3>
                                @if (!is_null($item->purchaseItem->item_summary))
                                    <table>
                                        <tr>
                                            <td class="item-summary  description word-break border-top-0 border-right-0 border-left-0 border-bottom-0" style="color:#555555;">
                                                {!! nl2br(strip_tags($item->purchaseItem->item_summary, ['p', 'b', 'strong', 'a'])) !!}</td>
                                        </tr>
                                    </table>
                                @endif
                            </td>
                            <td class="qty text-green">
                                <h3>{{ $item->quantity_received }}<br><span class="item-summary" style="color:#555555;">{{ $item->purchaseItem->unit->unit_type }}</h3>
                            </td>
                        </tr>
                    @endforeach
                @else
                    {{-- Show items from Purchase Order as we don't have separate items for DO yet --}}
                    @if ($delivery->purchaseOrder)
                        @foreach ($delivery->purchaseOrder->items as $item)
                            @if ($item->type == 'item')
                                <tr style="page-break-inside: avoid;">
                                    <td class="no background-green">{{ ++$count }}</td>
                                    <td class="desc text-green">
                                        <h3 class="description">{{ $item->item_name }}</h3>
                                        @if (!is_null($item->item_summary))
                                            <table>
                                                <tr>
                                                    <td class="item-summary  description word-break border-top-0 border-right-0 border-left-0 border-bottom-0" style="color:#555555;">
                                                        {!! nl2br(strip_tags($item->item_summary, ['p', 'b', 'strong', 'a'])) !!}</td>
                                                </tr>
                                            </table>
                                        @endif
                                    </td>
                                    <td class="qty text-green">
                                        <h3>{{ $item->quantity }}<br><span class="item-summary" style="color:#555555;">{{ $item->unit->unit_type }}</h3>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                @endif
            </tbody>
        </table>

        <p id="notes" class="word-break description">
        <div>
            @if ($delivery->purchaseOrder && !is_null($delivery->purchaseOrder->note))
                <b>@lang('app.note')</b><br>{!! nl2br($delivery->purchaseOrder->note) !!}<br>
            @endif
        </div>
        </p>

    </main>
</body>

</html>
