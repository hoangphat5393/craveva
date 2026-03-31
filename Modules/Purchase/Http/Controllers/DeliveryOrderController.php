<?php

namespace Modules\Purchase\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\Request;
use Modules\Purchase\DataTables\DeliveryOrderDataTable;
use Modules\Purchase\Entities\PurchaseOrder;
use Modules\Purchase\Services\GrnService;
use Modules\Purchase\Support\FlowPermission;
use Modules\Purchase\Support\GrnRuntime;

class DeliveryOrderController extends AccountBaseController
{
    private function grnRouteName(string $action): string
    {
        $prefix = config('purchase.flow_naming_mode', 'compat_v2') === 'legacy' ? 'delivery-orders' : 'grn';

        return $prefix . '.' . $action;
    }

    private function grnTitleKey(): string
    {
        return config('purchase.flow_naming_mode', 'compat_v2') === 'legacy'
            ? 'purchase::app.menu.deliveryOrders'
            : 'purchase::app.menu.goodsReceivedNote';
    }

    public function index(DeliveryOrderDataTable $dataTable)
    {
        abort_403(! FlowPermission::allowsAlias('grn.view'));
        $this->pageTitle = $this->grnTitleKey();

        return $dataTable->render('purchase::delivery-order.index', $this->data);
    }

    public function show($id)
    {
        abort_403(! FlowPermission::allowsAlias('grn.view'));
        $this->delivery = $this->queryByCompany()->with(
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
        abort_403(! FlowPermission::allowsAlias('grn.create') && ! FlowPermission::allowsAlias('grn.update'));
        $purchaseOrderId = $request->get('purchase_order_id');

        $this->items = collect();

        if ($purchaseOrderId) {
            $purchaseOrder = PurchaseOrder::with('items')->findOrFail($purchaseOrderId);
            $this->items = $purchaseOrder->items;
        }

        $html = view('purchase::delivery-order.ajax.items', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'html' => $html]);
    }

    public function changeStatus($id, Request $request, GrnService $grnService)
    {
        abort_403(! FlowPermission::allowsAlias('grn.change_status'));
        $request->validate([
            'status' => 'required|in:draft,inbound,received',
        ]);

        $delivery = $this->queryByCompany()->findOrFail($id);
        $error = $grnService->changeStatus($delivery, (string) $request->status);
        if ($error) {
            return Reply::error(__($error));
        }

        return Reply::success(__('messages.updateSuccess'));
    }

    public function destroy($id)
    {
        abort_403(! FlowPermission::allowsAlias('grn.delete'));
        $delivery = $this->queryByCompany()->findOrFail($id);
        $delivery->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function create()
    {
        abort_403(! FlowPermission::allowsAlias('grn.create'));
        $this->pageTitle = __('app.add') . ' ' . __($this->grnTitleKey());
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
        abort_403(! FlowPermission::allowsAlias('grn.update'));
        $this->pageTitle = __('app.edit') . ' ' . __($this->grnTitleKey());
        $this->delivery = $this->queryByCompany()->with(['items.purchaseItem.unit', 'purchaseOrder'])->findOrFail($id);
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
        abort_403(! FlowPermission::allowsAlias('grn.update'));
        $request->validate([
            'delivery_number' => 'required',
            'delivery_date' => 'required|date',
            'status' => 'required',
        ]);

        $delivery = $this->queryByCompany()->findOrFail($id);
        app(GrnService::class)->update($delivery, $this->buildDeliveryPayload($request, $delivery->type ?? 'inbound'));

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route($this->grnRouteName('index'))]);
    }

    public function store(Request $request)
    {
        abort_403(! FlowPermission::allowsAlias('grn.create'));
        $request->validate([
            'delivery_number' => 'required',
            'delivery_date' => 'required|date',
            'status' => 'required',
        ]);

        app(GrnService::class)->create(
            $this->buildDeliveryPayload($request, 'inbound'),
            $this->company ? $this->company->id : null
        );

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route($this->grnRouteName('index'))]);
    }

    public function download($id)
    {
        abort_403(! FlowPermission::allowsAlias('grn.view'));
        $this->delivery = $this->queryByCompany()->with('items', 'items.purchaseItem', 'purchaseOrder', 'purchaseOrder.items', 'purchaseOrder.items.unit', 'purchaseOrder.vendor', 'purchaseOrder.address', 'warehouse')->findOrFail($id);
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
        $headerModelClass = GrnRuntime::headerModelClass();
        $numberColumn = GrnRuntime::numberColumn();
        $query = $headerModelClass::query();
        if ($this->company) {
            $query->where('company_id', $this->company->id);
        }

        $lastNumber = (string) ($query->orderByDesc('id')->value($numberColumn) ?? '');
        if ($lastNumber !== '' && preg_match('/(\d+)$/', $lastNumber, $matches)) {
            $digits = $matches[1];
            $next = (int) $digits + 1;

            return str_pad((string) $next, max(strlen($digits), 3), '0', STR_PAD_LEFT);
        }

        return '001';
    }

    private function buildDeliveryPayload(Request $request, string $defaultType): array
    {
        $purchaseOrderId = $request->purchase_order_id;
        $po = $purchaseOrderId ? PurchaseOrder::find($purchaseOrderId) : null;
        $payload = [
            'purchase_order_id' => $purchaseOrderId,
            'warehouse_id' => $request->filled('warehouse_id') ? (int) $request->warehouse_id : ($po?->warehouse_id),
            'type' => $request->input('type', $defaultType),
            'delivery_number' => $request->delivery_number,
            'delivery_date' => Carbon::createFromFormat(company()->date_format, $request->delivery_date)->format('Y-m-d'),
            'status' => $request->status,
            'erp_shipment_reference' => $request->erp_shipment_reference,
            'wms_shipment_reference' => $request->wms_shipment_reference,
            'delivery_fee' => $request->filled('delivery_fee') ? (float) $request->delivery_fee : null,
        ];

        if ($request->has('item_id') && is_array($request->item_id)) {
            $payload['item_id'] = $request->item_id;
            $payload['product_id'] = $request->product_id ?? [];
            $payload['quantity_ordered'] = $request->quantity_ordered ?? [];
            $payload['quantity_received'] = $request->quantity_received ?? [];
            $payload['batch_number'] = array_map(
                fn($batch) => $this->normalizeBatch($batch),
                $request->batch_number ?? []
            );
            $payload['expiry_date'] = array_map(
                fn($expiry) => $this->parseDoExpiryInput($expiry),
                $request->expiry_date ?? []
            );
            $payload['picking_rule_applied'] = array_map(
                fn($rule) => $this->normalizePickingRule($rule),
                $request->picking_rule_applied ?? []
            );
        }

        return $payload;
    }

    private function queryByCompany()
    {
        $headerModelClass = GrnRuntime::headerModelClass();
        $q = $headerModelClass::query();

        if ($this->company) {
            $q->where('company_id', $this->company->id);
        }

        return $q;
    }
}
