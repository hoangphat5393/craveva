@php($grnLabelKey = config('purchase.flow_naming_mode', 'compat_v2') === 'legacy' ? 'purchase::app.menu.deliveryOrders' : 'purchase::app.menu.goodsReceivedNote')
<style>
    #logo {
        height: 50px;
    }
</style>

<div class="card border-0 invoice">
    <div class="card-body">
        <div class="invoice-table-wrapper">
            <table width="100%">
                <tr class="inv-logo-heading">
                    <td>
                        <img src="{{ invoice_setting()->logo_url }}" alt="{{ mb_ucwords(company()->company_name) }}" id="logo" />
                    </td>
                    <td align="right" class="font-weight-bold f-21 text-dark text-uppercase mt-4 mt-lg-0 mt-md-0">
                        @lang($grnLabelKey)
                    </td>
                </tr>
                <tr class="inv-num">
                    <td class="f-14 text-dark">
                        <p class="mt-3 mb-0">
                            {{ mb_ucwords(company()->company_name) }}<br>
                            @if ($delivery->purchaseOrder && $delivery->purchaseOrder->address)
                                {!! nl2br($delivery->purchaseOrder->address->address) !!}<br>
                            @endif
                            {{ company()->company_phone }}
                        </p>
                        <br>
                    </td>
                    <td align="right">
                        <table class="inv-num-date text-dark f-13 mt-3">
                            <tr>
                                <td class="bg-light-grey border-right-0 f-w-500">
                                    @lang('purchase::app.deliveryOrderNumber')</td>
                                <td class="border-left-0">{{ $delivery->delivery_number }}</td>
                            </tr>
                            <tr>
                                <td class="bg-light-grey border-right-0 f-w-500">
                                    @lang('app.date')</td>
                                <td class="border-left-0">
                                    {{ $delivery->delivery_date ? \Carbon\Carbon::parse($delivery->delivery_date)->translatedFormat(company()->date_format) : '----' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="bg-light-grey border-right-0 f-w-500">
                                    @lang('app.status')</td>
                                <td class="border-left-0">
                                    @lang('purchase::modules.deliveryOrder.' . $delivery->status)
                                </td>
                            </tr>
                            <tr>
                                <td class="bg-light-grey border-right-0 f-w-500">
                                    @lang('purchase::app.menu.purchaseOrder')</td>
                                <td class="border-left-0">
                                    {{ optional($delivery->purchaseOrder)->purchase_order_number }}
                                </td>
                            </tr>
                            @if ($delivery->warehouse)
                                <tr>
                                    <td class="bg-light-grey border-right-0 f-w-500">
                                        @lang('purchase::modules.deliveryOrder.warehouse')</td>
                                    <td class="border-left-0">
                                        {{ $delivery->warehouse->name }}@if ($delivery->warehouse->code)
                                            ({{ $delivery->warehouse->code }})
                                        @endif
                                    </td>
                                </tr>
                            @endif
                            @if ($delivery->delivery_fee !== null)
                                <tr>
                                    <td class="bg-light-grey border-right-0 f-w-500">
                                        @lang('purchase::modules.deliveryOrder.deliveryFee')</td>
                                    <td class="border-left-0">
                                        {{ number_format((float) $delivery->delivery_fee, 2) }}
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </td>
                </tr>
                <tr>
                    <td height="20"></td>
                </tr>
            </table>

            <table width="100%">
                <tr class="inv-unpaid">
                    <td class="f-14 text-dark">
                        <p class="mb-0 text-left">
                            <span class="text-dark-grey">@lang('modules.invoices.billedFrom')</span><br>
                            @if ($delivery->purchaseOrder && $delivery->purchaseOrder->vendor && $delivery->purchaseOrder->vendor->primary_name)
                                {{ $delivery->purchaseOrder->vendor->primary_name }}<br>
                            @endif
                            @if ($delivery->purchaseOrder && $delivery->purchaseOrder->vendor && $delivery->purchaseOrder->vendor->email)
                                {{ $delivery->purchaseOrder->vendor->email }}<br>
                            @endif
                            @if ($delivery->purchaseOrder && $delivery->purchaseOrder->vendor && $delivery->purchaseOrder->vendor->phone)
                                {{ $delivery->purchaseOrder->vendor->phone }}<br>
                            @endif
                            @if ($delivery->purchaseOrder && $delivery->purchaseOrder->vendor && $delivery->purchaseOrder->vendor->company_name)
                                {{ mb_ucwords($delivery->purchaseOrder->vendor->company_name) }}<br>
                            @endif
                            @if ($delivery->purchaseOrder && $delivery->purchaseOrder->vendor && $delivery->purchaseOrder->vendor->billing_address)
                                {{ $delivery->purchaseOrder->vendor->billing_address }}<br>
                            @endif
                        </p>
                    </td>
                </tr>
                <tr>
                    <td height="30" colspan="2"></td>
                </tr>
            </table>

            <table width="100%" class="inv-desc d-none d-lg-table d-md-table">
                <tr>
                    <td colspan="3">
                        <table class="inv-detail f-14 table-responsive-sm" width="100%">
                            <tr class="i-d-heading bg-light-grey text-dark-grey font-weight-bold">
                                <td class="border-right-0" width="32%">@lang('app.description')</td>
                                <td class="border-right-0 border-left-0" align="right" width="12%">
                                    @lang('purchase::modules.reports.quantityOrdered')
                                </td>
                                <td class="border-left-0" align="right" width="12%">
                                    @lang('purchase::modules.deliveryOrder.quantityReceived')
                                </td>
                                <td class="border-left-0" width="14%">@lang('purchase::modules.deliveryOrder.batchLot')</td>
                                <td class="border-left-0" width="15%">@lang('purchase::modules.deliveryOrder.expiryDate')</td>
                                <td class="border-left-0" width="15%">@lang('purchase::modules.deliveryOrder.pickingRule')</td>
                            </tr>

                            @if ($delivery->items->count() > 0)
                                @foreach ($delivery->items as $item)
                                    <tr class="text-dark font-weight-semibold f-13">
                                        <td>{{ optional($item->purchaseItem)->item_name }}</td>
                                        <td align="right">
                                            {{ $item->quantity_ordered }}
                                            @if ($item->purchaseItem && $item->purchaseItem->unit)
                                                <br><span class="f-11 text-dark-grey">{{ $item->purchaseItem->unit->unit_type }}</span>
                                            @endif
                                        </td>
                                        <td align="right">
                                            {{ $item->quantity_received }}
                                            @if ($item->purchaseItem && $item->purchaseItem->unit)
                                                <br><span class="f-11 text-dark-grey">{{ $item->purchaseItem->unit->unit_type }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->batch_number ?: '—' }}</td>
                                        <td>
                                            {{ $item->expiry_date ? \Carbon\Carbon::parse($item->expiry_date)->translatedFormat(company()->date_format) : '—' }}
                                        </td>
                                        <td>{{ $item->picking_rule_applied ?: '—' }}</td>
                                    </tr>
                                    @if ($item->purchaseItem && $item->purchaseItem->item_summary)
                                        <tr class="text-dark f-12">
                                            <td colspan="6" class="border-bottom-0">
                                                {!! nl2br(strip_tags($item->purchaseItem->item_summary, ['p', 'b', 'strong', 'a'])) !!}
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @elseif($delivery->purchaseOrder && $delivery->purchaseOrder->items->count() > 0)
                                @foreach ($delivery->purchaseOrder->items as $item)
                                    @if ($item->type == 'item')
                                        <tr class="text-dark font-weight-semibold f-13">
                                            <td>{{ $item->item_name }}</td>
                                            <td align="right">
                                                {{ $item->quantity }}
                                                @if ($item->unit)
                                                    <br><span class="f-11 text-dark-grey">{{ $item->unit->unit_type }}</span>
                                                @endif
                                            </td>
                                            <td align="right">
                                                {{ $item->quantity }}
                                                @if ($item->unit)
                                                    <br><span class="f-11 text-dark-grey">{{ $item->unit->unit_type }}</span>
                                                @endif
                                            </td>
                                            <td colspan="3">—</td>
                                        </tr>
                                        @if ($item->item_summary)
                                            <tr class="text-dark f-12">
                                                <td colspan="6" class="border-bottom-0">
                                                    {!! nl2br(strip_tags($item->item_summary, ['p', 'b', 'strong', 'a'])) !!}
                                                </td>
                                            </tr>
                                        @endif
                                    @endif
                                @endforeach
                            @endif
                        </table>
                    </td>
                </tr>
            </table>

            <table width="100%" class="inv-desc-mob d-block d-lg-none d-md-none">
                @if ($delivery->items->count() > 0)
                    @foreach ($delivery->items as $item)
                        <tr>
                            <th width="50%" class="bg-light-grey text-dark-grey font-weight-bold">
                                @lang('app.description')</th>
                            <td class="p-0 ">
                                <table>
                                    <tr width="100%" class="font-weight-semibold f-13">
                                        <td class="border-left-0 border-right-0 border-top-0">
                                            {{ optional($item->purchaseItem)->item_name }}</td>
                                    </tr>
                                    @if ($item->purchaseItem && $item->purchaseItem->item_summary)
                                        <tr>
                                            <td class="border-left-0 border-right-0 border-bottom-0 f-12">
                                                {!! nl2br(strip_tags($item->purchaseItem->item_summary, ['p', 'b', 'strong', 'a'])) !!}
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <th width="50%" class="bg-light-grey text-dark-grey font-weight-bold">
                                @lang('purchase::modules.reports.quantityOrdered')</th>
                            <td width="50%">{{ $item->quantity_ordered }}</td>
                        </tr>
                        <tr>
                            <th width="50%" class="bg-light-grey text-dark-grey font-weight-bold">
                                @lang('purchase::modules.deliveryOrder.quantityReceived')</th>
                            <td width="50%">{{ $item->quantity_received }}</td>
                        </tr>
                    @endforeach
                @elseif($delivery->purchaseOrder && $delivery->purchaseOrder->items->count() > 0)
                    @foreach ($delivery->purchaseOrder->items as $item)
                        @if ($item->type == 'item')
                            <tr>
                                <th width="50%" class="bg-light-grey text-dark-grey font-weight-bold">
                                    @lang('app.description')</th>
                                <td class="p-0 ">
                                    <table>
                                        <tr width="100%" class="font-weight-semibold f-13">
                                            <td class="border-left-0 border-right-0 border-top-0">
                                                {{ $item->item_name }}</td>
                                        </tr>
                                        @if ($item->item_summary)
                                            <tr>
                                                <td class="border-left-0 border-right-0 border-bottom-0 f-12">
                                                    {!! nl2br(strip_tags($item->item_summary, ['p', 'b', 'strong', 'a'])) !!}
                                                </td>
                                            </tr>
                                        @endif
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <th width="50%" class="bg-light-grey text-dark-grey font-weight-bold">
                                    @lang('purchase::reports.quantityOrdered')</th>
                                <td width="50%">{{ $item->quantity }}</td>
                            </tr>
                            <tr>
                                <th width="50%" class="bg-light-grey text-dark-grey font-weight-bold">
                                    @lang('purchase::deliveryOrder.quantityReceived')</th>
                                <td width="50%">{{ $item->quantity }}</td>
                            </tr>
                        @endif
                    @endforeach
                @endif
            </table>

            @php
                $deliveryOrderTermsText = trim((string) ($purchaseSetting->delivery_order_terms ?? '')) !== '' ? (string) $purchaseSetting->delivery_order_terms : (string) ($purchaseSetting->purchase_terms ?? '');
            @endphp
            <table width="100%" class="mt-4 inv-note">
                <tr>
                    <td height="30" colspan="2"></td>
                </tr>
                <tr>
                    <td>@lang('app.note')</td>
                    <td style="text-align: right;">@lang('purchase::modules.purchaseSettings.deliveryOrderTerms')</td>
                </tr>
                <tr>
                    <td style="vertical-align: text-top">
                        <p class="text-dark-grey">
                            @if ($delivery->purchaseOrder && $delivery->purchaseOrder->note)
                                {!! nl2br($delivery->purchaseOrder->note) !!}
                            @else
                                --
                            @endif
                        </p>
                    </td>
                    <td style="text-align: right;">
                        <p class="text-dark-grey">{!! nl2br($deliveryOrderTermsText) !!}</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
