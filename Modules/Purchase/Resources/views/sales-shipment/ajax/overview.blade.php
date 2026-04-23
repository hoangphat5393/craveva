@php
    $statusClass = match ($shipment->status) {
        'draft' => 'text-dark border-dark',
        'confirmed' => 'text-info border-info',
        'shipped' => 'text-primary border-primary',
        'delivered' => 'text-success border-success',
        'cancelled' => 'text-danger border-danger',
        default => 'text-dark border-dark',
    };
    $canUpdate = \Modules\Purchase\Support\FlowPermission::allowsAlias('sales_do.update');
    $canShip = \Modules\Purchase\Support\FlowPermission::allowsAlias('sales_do.ship');
    $canCancel = \Modules\Purchase\Support\FlowPermission::allowsAlias('sales_do.cancel');
    $canAddInvoice = in_array(user()->permission('add_invoices'), ['all', 'added']);
    $salesDoRoutePrefix = config('purchase.flow_naming_mode', 'compat_v2') === 'legacy' ? 'sales-shipments' : 'sales-do';
@endphp

<div class="card border-0 invoice">
    <div class="card-body">
        <div class="invoice-table-wrapper">
            <table width="100%">
                <tr>
                    <td class="f-14 text-dark">
                        <h4 class="mb-2">{{ $shipment->shipment_number }}</h4>
                        <p class="mb-0">
                            @lang('app.order'):
                            <a href="{{ route('orders.show', $shipment->order_id) }}" class="text-dark-grey">
                                {{ $shipment->order?->order_number ?: '#' . $shipment->order_id }}
                            </a>
                        </p>
                        <p class="mb-0">@lang('app.date'): {{ $shipment->shipment_date?->translatedFormat(company()->date_format) }}</p>
                        @if ($shipment->warehouse)
                            @php
                                $wh = $shipment->warehouse;
                                $whDisplay = filled($wh->code) ? $wh->name . ' (' . $wh->code . ')' : $wh->name;
                            @endphp
                            <p class="mb-0">@lang('purchase::modules.deliveryOrder.warehouse'): {{ $whDisplay }}</p>
                        @endif
                    </td>
                    <td align="right">
                        <span class="unpaid rounded f-14 {{ $statusClass }}">
                            @lang('purchase::modules.salesShipment.' . $shipment->status)
                        </span>
                    </td>
                </tr>
            </table>

            <table width="100%" class="inv-desc d-none d-lg-table d-md-table mt-3">
                <tr>
                    <td>
                        <table class="inv-detail f-14 table-responsive-sm" width="100%">
                            <tr class="i-d-heading bg-light-grey text-dark-grey font-weight-bold">
                                <td class="border-right-0">@lang('app.description')</td>
                                <td class="border-left-0 border-right-0">@lang('purchase::app.sku')</td>
                                <td class="border-left-0 border-right-0" align="right">@lang('modules.invoices.qty')</td>
                                <td class="border-left-0 border-right-0" align="right">@lang('purchase::app.shipQty')</td>
                                <td class="border-left-0">@lang('purchase::app.batchNumber')</td>
                            </tr>
                            @foreach ($shipment->items as $item)
                                @php
                                    $oi = $item->orderItem;
                                    $overviewSku = $oi?->sku ?: $oi?->product?->sku ?? null;
                                @endphp
                                <tr class="text-dark font-weight-semibold f-13">
                                    <td>{{ $oi?->item_name ?: '#' . $item->order_item_id }}</td>
                                    <td class="text-dark-grey f-12">{{ $overviewSku ?: '—' }}</td>
                                    <td align="right">{{ number_format((float) $item->quantity_ordered, 2) }}</td>
                                    <td align="right">{{ number_format((float) $item->quantity_shipped, 2) }}</td>
                                    <td>{{ $item->batch_display }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>
            </table>

            @if ($shipment->notes)
                <table class="inv-note mt-3">
                    <tr>
                        <td><strong>@lang('app.note')</strong></td>
                    </tr>
                    <tr>
                        <td>
                            <p class="text-dark-grey mb-0">{!! nl2br(e($shipment->notes)) !!}</p>
                        </td>
                    </tr>
                </table>
            @endif
        </div>
    </div>

    <div class="card-footer bg-white border-0 d-flex justify-content-start py-0 py-lg-4 py-md-4 mb-4 mb-lg-3 mb-md-3">
        <div class="d-flex">
            <div class="inv-action mr-3 mr-lg-3 mr-md-3 dropup">
                <button class="dropdown-toggle btn-primary" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    @lang('app.action') <span><i class="fa fa-chevron-up f-15"></i></span>
                </button>
                <ul class="dropdown-menu" tabindex="0">
                    @if ($canUpdate && !in_array($shipment->status, ['shipped', 'delivered', 'cancelled'], true))
                        <li>
                            <a class="dropdown-item f-14 text-dark openRightModal" href="{{ route($salesDoRoutePrefix . '.edit', $shipment->id) }}">
                                <i class="fa fa-edit f-w-500 mr-2 f-11"></i> @lang('app.edit')
                            </a>
                        </li>
                    @endif
                    @if ($canShip && $shipment->status === 'draft')
                        <li><a class="dropdown-item f-14 text-dark shipment-action" data-action="confirm" href="javascript:;"><i class="fa fa-check mr-2"></i>@lang('app.confirm')</a></li>
                    @endif
                    @if ($canShip && in_array($shipment->status, ['draft', 'confirmed'], true))
                        <li><a class="dropdown-item f-14 text-dark shipment-action" data-action="ship" href="javascript:;"><i class="fa fa-truck mr-2"></i>@lang('purchase::app.ship')</a></li>
                    @endif
                    @if ($canShip && $shipment->status === 'shipped')
                        <li><a class="dropdown-item f-14 text-dark shipment-action" data-action="deliver" href="javascript:;"><i class="fa fa-box mr-2"></i>@lang('purchase::modules.salesShipment.delivered')</a></li>
                    @endif
                    @if ($canCancel && in_array($shipment->status, ['shipped', 'delivered'], true))
                        <li><a class="dropdown-item f-14 text-dark shipment-action" data-action="reverse" href="javascript:;"><i class="fa fa-undo mr-2"></i>@lang('purchase::modules.salesShipment.reverse')</a></li>
                    @endif
                    @if ($canCancel && $shipment->status !== 'cancelled')
                        <li><a class="dropdown-item f-14 text-dark shipment-action" data-action="cancel" href="javascript:;"><i class="fa fa-ban mr-2"></i>@lang('app.cancel')</a></li>
                    @endif
                    @if ($canAddInvoice && in_array($shipment->status, ['shipped', 'delivered'], true))
                        <li>
                            <a class="dropdown-item f-14 text-dark" href="{{ route('invoices.create', ['sales_do_id' => $shipment->id, 'order_id' => $shipment->order_id]) }}">
                                <i class="fa fa-receipt mr-2"></i> @lang('modules.invoices.addInvoice')
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            <x-forms.button-cancel :link="route($salesDoRoutePrefix . '.index')" class="border-0 mr-3">@lang('app.back')</x-forms.button-cancel>
        </div>
    </div>
</div>

<script>
    $('body').off('click.shipment-action').on('click.shipment-action', '.shipment-action', function() {
        const action = $(this).data('action');
        const url = "{{ url('/account') }}/{{ $salesDoRoutePrefix }}/{{ $shipment->id }}/" + action;
        const body = '_token=' + encodeURIComponent("{{ csrf_token() }}");
        const labels = {
            confirm: "@lang('app.confirm')",
            ship: "@lang('purchase::app.ship')",
            deliver: "@lang('purchase::modules.salesShipment.delivered')",
            reverse: "@lang('purchase::modules.salesShipment.reverse')",
            cancel: "@lang('app.cancel')"
        };

        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: labels[action] || action,
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('app.yes')",
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }
            $.easyBlockUI('.content-wrapper');
            window.apiHttp.postUrlEncoded(url, body).then(function(response) {
                if (response.status === 'success') {
                    window.location.reload();
                }
            }).catch(function(err) {
                Swal.fire({
                    icon: 'error',
                    text: err.message,
                    toast: true,
                    position: 'top-end',
                    timer: 4000,
                    showConfirmButton: false
                });
            }).finally(function() {
                $.easyUnblockUI('.content-wrapper');
            });
        });
    });
</script>
