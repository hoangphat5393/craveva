<?php

namespace Modules\Purchase\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\DeliveryOrder;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\Request;
use Modules\Purchase\DataTables\DeliveryOrderDataTable;
use Modules\Purchase\Entities\DeliveryOrderItem;
use Modules\Purchase\Entities\PurchaseOrder;

class DeliveryOrderController extends AccountBaseController
{
    public function index(DeliveryOrderDataTable $dataTable)
    {
        $this->pageTitle = 'purchase::app.menu.deliveryOrders';

        return $dataTable->render('purchase::delivery-order.index', $this->data);
    }

    public function show($id)
    {
        $this->delivery = DeliveryOrder::with(
            'items',
            'items.purchaseItem',
            'items.purchaseItem.unit',
            'purchaseOrder',
            'purchaseOrder.items',
            'purchaseOrder.items.unit',
            'purchaseOrder.vendor',
            'purchaseOrder.address',
            'warehouse'
        )->findOrFail($id);

        $this->pageTitle = $this->delivery->delivery_number;
        $this->settings = company();

        $tab = request('tab');

        switch ($tab) {
            default:
                $this->view = 'purchase::delivery-order.ajax.overview';
                break;
        }

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->activeTab = $tab ?: 'overview';

        return view('purchase::delivery-order.show', $this->data);
    }

    public function getItems(Request $request)
    {
        $purchaseOrderId = $request->get('purchase_order_id');

        $this->items = collect();

        if ($purchaseOrderId) {
            $purchaseOrder = PurchaseOrder::with('items')->findOrFail($purchaseOrderId);
            $this->items = $purchaseOrder->items;
        }

        $html = view('purchase::delivery-order.ajax.items', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'html' => $html]);
    }

    public function changeStatus($id, Request $request)
    {
        $request->validate([
            'status' => 'required|in:draft,inbound,received',
        ]);

        $query = DeliveryOrder::query();
        if ($this->company) {
            $query->where('company_id', $this->company->id);
        }

        $delivery = $query->findOrFail($id);
        $delivery->status = $request->status;
        $delivery->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    public function destroy($id)
    {
        $query = DeliveryOrder::query();
        if ($this->company) {
            $query->where('company_id', $this->company->id);
        }
        $delivery = $query->findOrFail($id);
        $delivery->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function create()
    {
        $this->pageTitle = __('app.add') . ' ' . __('purchase::app.menu.deliveryOrders');
        $this->purchaseOrders = PurchaseOrder::where('company_id', $this->company ? $this->company->id : null)->get();
        $this->warehouses = $this->warehouseList();
        $this->nextDeliveryNumber = $this->nextDeliveryNumber();

        if (request()->ajax()) {
            $html = view('purchase::delivery-order.ajax.create', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'purchase::delivery-order.ajax.create';

        return view('purchase::delivery-order.create', $this->data);
    }

    public function edit($id)
    {
        $this->pageTitle = __('app.edit') . ' ' . __('purchase::app.menu.deliveryOrders');
        $this->delivery = DeliveryOrder::with(['items.purchaseItem.unit', 'purchaseOrder'])->findOrFail($id);
        $this->purchaseOrders = PurchaseOrder::where('company_id', $this->company ? $this->company->id : null)->get();
        $this->warehouses = $this->warehouseList();
        $this->deliveryItems = $this->delivery->items;

        if (request()->ajax()) {
            $html = view('purchase::delivery-order.ajax.edit', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'purchase::delivery-order.ajax.edit';

        return view('purchase::delivery-order.create', $this->data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'delivery_number' => 'required',
            'delivery_date' => 'required|date',
            'status' => 'required',
        ]);

        $delivery = DeliveryOrder::findOrFail($id);
        $delivery->purchase_order_id = $request->purchase_order_id;
        $po = $request->purchase_order_id ? PurchaseOrder::find($request->purchase_order_id) : null;
        $delivery->warehouse_id = $request->filled('warehouse_id') ? (int) $request->warehouse_id : ($po?->warehouse_id);
        $delivery->type = $request->input('type', $delivery->type ?? 'inbound');
        $delivery->delivery_number = $request->delivery_number;
        $delivery->delivery_date = Carbon::createFromFormat(company()->date_format, $request->delivery_date)->format('Y-m-d');
        $delivery->status = $request->status;
        $delivery->erp_shipment_reference = $request->erp_shipment_reference;
        $delivery->wms_shipment_reference = $request->wms_shipment_reference;
        $delivery->delivery_fee = $request->filled('delivery_fee') ? (float) $request->delivery_fee : null;
        $delivery->save();

        if ($request->has('item_id')) {
            DeliveryOrderItem::where('delivery_order_id', $delivery->id)->delete();

            foreach ($request->item_id as $key => $itemId) {
                DeliveryOrderItem::create([
                    'delivery_order_id' => $delivery->id,
                    'purchase_item_id' => $itemId,
                    'product_id' => $request->product_id[$key] ?? null,
                    'quantity_ordered' => $request->quantity_ordered[$key] ?? 0,
                    'quantity_received' => $request->quantity_received[$key] ?? 0,
                    'batch_number' => $this->normalizeBatch($request->batch_number[$key] ?? null),
                    'expiry_date' => $this->parseDoExpiryInput($request->expiry_date[$key] ?? null),
                    'picking_rule_applied' => $this->normalizePickingRule($request->picking_rule_applied[$key] ?? null),
                ]);
            }
        }

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('delivery-orders.index')]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'delivery_number' => 'required',
            'delivery_date' => 'required|date',
            'status' => 'required',
        ]);

        $delivery = new DeliveryOrder;
        $delivery->company_id = $this->company ? $this->company->id : null;
        $delivery->purchase_order_id = $request->purchase_order_id;
        $po = $request->purchase_order_id ? PurchaseOrder::find($request->purchase_order_id) : null;
        $delivery->warehouse_id = $request->filled('warehouse_id') ? (int) $request->warehouse_id : ($po?->warehouse_id);
        $delivery->type = $request->input('type', 'inbound');
        $delivery->delivery_number = $request->delivery_number;
        $delivery->delivery_date = Carbon::createFromFormat(company()->date_format, $request->delivery_date)->format('Y-m-d');
        $delivery->status = $request->status;
        $delivery->erp_shipment_reference = $request->erp_shipment_reference;
        $delivery->wms_shipment_reference = $request->wms_shipment_reference;
        $delivery->delivery_fee = $request->filled('delivery_fee') ? (float) $request->delivery_fee : null;
        $delivery->save();

        if ($request->has('item_id')) {
            foreach ($request->item_id as $key => $itemId) {
                DeliveryOrderItem::create([
                    'delivery_order_id' => $delivery->id,
                    'purchase_item_id' => $itemId,
                    'product_id' => $request->product_id[$key] ?? null,
                    'quantity_ordered' => $request->quantity_ordered[$key] ?? 0,
                    'quantity_received' => $request->quantity_received[$key] ?? 0,
                    'batch_number' => $this->normalizeBatch($request->batch_number[$key] ?? null),
                    'expiry_date' => $this->parseDoExpiryInput($request->expiry_date[$key] ?? null),
                    'picking_rule_applied' => $this->normalizePickingRule($request->picking_rule_applied[$key] ?? null),
                ]);
            }
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('delivery-orders.index')]);
    }

    public function download($id)
    {
        $this->delivery = DeliveryOrder::with('items', 'items.purchaseItem', 'purchaseOrder', 'purchaseOrder.items', 'purchaseOrder.items.unit', 'purchaseOrder.vendor', 'purchaseOrder.address', 'warehouse')->findOrFail($id);
        $this->company = $this->delivery->company;
        $this->invoiceSetting = $this->company->invoiceSetting;

        $pdf = app('dompdf.wrapper');
        $pdf->setOption('enable_php', true);
        $pdf->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        \Illuminate\Support\Facades\App::setLocale($this->invoiceSetting->locale ?? 'en');
        \Carbon\Carbon::setLocale($this->invoiceSetting->locale ?? 'en');

        $pdf->loadView('purchase::delivery-order.pdf.delivery-order-1', $this->data);

        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->getCanvas();
        $canvas->page_text(530, 820, 'Page {PAGE_NUM} of {PAGE_COUNT}', null, 10);

        $filename = 'delivery-order-' . $this->delivery->delivery_number;

        return $pdf->download($filename . '.pdf');
    }

    private function warehouseList(): \Illuminate\Support\Collection
    {
        if (! $this->company || ! class_exists(\Modules\Warehouse\Entities\Warehouse::class)) {
            return collect();
        }

        return \Modules\Warehouse\Entities\Warehouse::query()
            ->where('company_id', $this->company->id)
            ->orderBy('name')
            ->get();
    }

    private function normalizeBatch(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }
        $t = trim($raw);

        return $t === '' ? null : $t;
    }

    private function parseDoExpiryInput(?string $raw): ?string
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $str = trim($raw);
        $company = company();

        try {
            return Carbon::createFromFormat($company->date_format, $str)->format('Y-m-d');
        } catch (InvalidFormatException $e) {
            try {
                return Carbon::parse($str)->format('Y-m-d');
            } catch (\Throwable $e2) {
                return null;
            }
        }
    }

    private function normalizePickingRule(?string $raw): ?string
    {
        $r = strtoupper(trim((string) $raw));

        return in_array($r, ['FIFO', 'FEFO'], true) ? $r : null;
    }

    private function nextDeliveryNumber(): string
    {
        $query = DeliveryOrder::query();
        if ($this->company) {
            $query->where('company_id', $this->company->id);
        }

        $lastNumber = (string) ($query->orderByDesc('id')->value('delivery_number') ?? '');
        if ($lastNumber !== '' && preg_match('/(\d+)$/', $lastNumber, $matches)) {
            $digits = $matches[1];
            $next = (int) $digits + 1;

            return str_pad((string) $next, max(strlen($digits), 3), '0', STR_PAD_LEFT);
        }

        return '001';
    }
}
