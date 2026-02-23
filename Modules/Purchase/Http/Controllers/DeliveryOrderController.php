<?php

namespace Modules\Purchase\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use App\Models\DeliveryOrder;
use App\Models\Company;
use Illuminate\Contracts\Support\Renderable;
use Modules\Purchase\Entities\PurchaseOrder;
use Modules\Purchase\Entities\DeliveryOrderItem;
use App\Helper\Reply;
use Illuminate\Http\Request;
use Modules\Purchase\DataTables\DeliveryOrderDataTable;

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
            'purchaseOrder.address'
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
        $delivery = DeliveryOrder::findOrFail($id);
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

        $delivery = new DeliveryOrder();
        $getCustomFieldGroupsWithFields = $delivery->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

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
        $this->delivery = DeliveryOrder::with('items', 'items.purchaseItem')->findOrFail($id);
        $this->purchaseOrders = PurchaseOrder::where('company_id', $this->company ? $this->company->id : null)->get();

        $getCustomFieldGroupsWithFields = $this->delivery->getCustomFieldGroupsWithFields();
        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

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
        $delivery->delivery_number = $request->delivery_number;
        $delivery->delivery_date = \Carbon\Carbon::createFromFormat(company()->date_format, $request->delivery_date)->format('Y-m-d');
        $delivery->status = $request->status;
        $delivery->erp_shipment_reference = $request->erp_shipment_reference;
        $delivery->wms_shipment_reference = $request->wms_shipment_reference;
        $delivery->save();

        if ($request->custom_fields_data) {
            $delivery->updateCustomFieldData($request->custom_fields_data);
        }

        // Save Items
        if ($request->has('item_id')) {
            DeliveryOrderItem::where('delivery_order_id', $delivery->id)->delete();

            foreach ($request->item_id as $key => $itemId) {
                DeliveryOrderItem::create([
                    'delivery_order_id' => $delivery->id,
                    'purchase_item_id' => $itemId,
                    'product_id' => $request->product_id[$key],
                    'quantity_ordered' => $request->quantity_ordered[$key],
                    'quantity_received' => $request->quantity_received[$key],
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

        $delivery = new DeliveryOrder();
        $delivery->company_id = $this->company ? $this->company->id : null;
        $delivery->purchase_order_id = $request->purchase_order_id;
        $delivery->delivery_number = $request->delivery_number;
        $delivery->delivery_date = \Carbon\Carbon::createFromFormat(company()->date_format, $request->delivery_date)->format('Y-m-d');
        $delivery->status = $request->status;
        $delivery->erp_shipment_reference = $request->erp_shipment_reference;
        $delivery->wms_shipment_reference = $request->wms_shipment_reference;
        $delivery->save();

        if ($request->custom_fields_data) {
            $delivery->updateCustomFieldData($request->custom_fields_data);
        }

        // Save Items
        if ($request->has('item_id')) {
            foreach ($request->item_id as $key => $itemId) {
                DeliveryOrderItem::create([
                    'delivery_order_id' => $delivery->id,
                    'purchase_item_id' => $itemId,
                    'product_id' => $request->product_id[$key],
                    'quantity_ordered' => $request->quantity_ordered[$key],
                    'quantity_received' => $request->quantity_received[$key],
                ]);
            }
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('delivery-orders.index')]);
    }

    public function download($id)
    {
        $this->delivery = DeliveryOrder::with('items', 'items.purchaseItem', 'purchaseOrder', 'purchaseOrder.items', 'purchaseOrder.items.unit', 'purchaseOrder.vendor', 'purchaseOrder.address')->findOrFail($id);
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
}
