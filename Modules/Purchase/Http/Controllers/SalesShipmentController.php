<?php

namespace Modules\Purchase\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\Order;
use App\Models\OrderItems;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Purchase\DataTables\SalesShipmentDataTable;
use Modules\Purchase\Entities\SalesShipment;
use Modules\Purchase\Entities\SalesShipmentItem;
use Modules\Warehouse\Services\SalesShipmentStockService;

class SalesShipmentController extends AccountBaseController
{
    public function index(SalesShipmentDataTable $dataTable)
    {
        abort_403(user()->permission('view_sales_shipment') === 'none');
        $this->pageTitle = 'purchase::app.menu.salesShipments';

        return $dataTable->render('purchase::sales-shipment.index', $this->data);
    }

    public function create(Request $request)
    {
        abort_403(user()->permission('create_sales_shipment') === 'none');
        $this->pageTitle = __('app.add') . ' ' . __('purchase::app.menu.salesShipments');
        $this->warehouses = $this->warehouseList();
        $this->orders = Order::query()
            ->where('company_id', $this->company?->id)
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        $prefillOrderId = (int) $request->get('order_id', 0);
        $this->prefillOrder = $prefillOrderId > 0
            ? Order::query()->where('company_id', $this->company?->id)->find($prefillOrderId)
            : null;

        if (request()->ajax()) {
            $html = view('purchase::sales-shipment.ajax.create', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'purchase::sales-shipment.ajax.create';

        return view('purchase::sales-shipment.create', $this->data);
    }

    public function edit($id)
    {
        abort_403(user()->permission('update_sales_shipment') === 'none');
        $this->pageTitle = __('app.edit') . ' ' . __('purchase::app.menu.salesShipments');
        $this->shipment = $this->queryByCompany()->with(['items.orderItem', 'order'])->findOrFail($id);
        abort_if(in_array($this->shipment->status, ['shipped', 'delivered'], true), 403);

        $this->warehouses = $this->warehouseList();
        $this->orders = Order::query()
            ->where('company_id', $this->company?->id)
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        if (request()->ajax()) {
            $html = view('purchase::sales-shipment.ajax.edit', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'purchase::sales-shipment.ajax.edit';

        return view('purchase::sales-shipment.create', $this->data);
    }

    public function show($id)
    {
        abort_403(user()->permission('view_sales_shipment') === 'none');
        $this->shipment = $this->queryByCompany()
            ->with(['items.orderItem', 'order', 'warehouse'])
            ->findOrFail($id);
        $this->pageTitle = $this->shipment->shipment_number;
        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';
        $this->view = 'purchase::sales-shipment.ajax.overview';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('purchase::sales-shipment.show', $this->data);
    }

    public function getOrderItems(Request $request)
    {
        abort_403(user()->permission('create_sales_shipment') === 'none' && user()->permission('update_sales_shipment') === 'none');
        $orderId = (int) $request->get('order_id');
        $shipmentId = (int) $request->get('shipment_id', 0);

        $order = Order::query()
            ->where('company_id', $this->company?->id)
            ->with(['items.unit', 'clientdetails'])
            ->findOrFail($orderId);

        $this->shipment = $shipmentId > 0 ? $this->queryByCompany()->with('items')->find($shipmentId) : null;
        $this->order = $order;
        $this->remainingByItem = $this->remainingQtyByOrderItem($order->id, $shipmentId);
        $defaultWarehouseId = $this->resolveOrderDefaultWarehouseId($order);

        $html = view('purchase::sales-shipment.ajax.items', $this->data)->render();

        return Reply::dataOnly([
            'status' => 'success',
            'html' => $html,
            'defaultWarehouseId' => $defaultWarehouseId,
        ]);
    }

    public function store(Request $request)
    {
        abort_403(user()->permission('create_sales_shipment') === 'none');
        $payload = $this->validateForm($request);

        $shipment = DB::transaction(function () use ($payload) {
            $shipment = new SalesShipment;
            $shipment->company_id = $this->company?->id;
            $shipment->order_id = (int) $payload['order_id'];
            $shipment->warehouse_id = (int) $payload['warehouse_id'];
            $shipment->shipment_number = $payload['shipment_number'] ?: $this->nextShipmentNumber();
            $shipment->shipment_date = $payload['shipment_date'];
            $shipment->status = $payload['status'];
            $shipment->notes = $payload['notes'];
            $shipment->created_by = user()->id;
            $shipment->updated_by = user()->id;
            $shipment->save();

            $this->upsertItems($shipment, $payload);

            return $shipment;
        });

        return Reply::successWithData(__('messages.recordSaved'), [
            'redirectUrl' => route('sales-shipments.show', $shipment->id),
        ]);
    }

    public function update(Request $request, $id)
    {
        abort_403(user()->permission('update_sales_shipment') === 'none');
        $shipment = $this->queryByCompany()->with('items')->findOrFail($id);
        abort_if(in_array($shipment->status, ['shipped', 'delivered'], true), 403);

        $payload = $this->validateForm($request, $shipment->id);

        DB::transaction(function () use ($shipment, $payload) {
            $shipment->order_id = (int) $payload['order_id'];
            $shipment->warehouse_id = (int) $payload['warehouse_id'];
            $shipment->shipment_number = $payload['shipment_number'] ?: $shipment->shipment_number;
            $shipment->shipment_date = $payload['shipment_date'];
            $shipment->status = $payload['status'];
            $shipment->notes = $payload['notes'];
            $shipment->updated_by = user()->id;
            $shipment->save();

            $this->upsertItems($shipment, $payload);
        });

        return Reply::successWithData(__('messages.updateSuccess'), [
            'redirectUrl' => route('sales-shipments.show', $shipment->id),
        ]);
    }

    public function confirm($id)
    {
        abort_403(user()->permission('update_sales_shipment') === 'none');
        $shipment = $this->queryByCompany()->with('items')->findOrFail($id);

        if ($shipment->status !== 'draft') {
            return Reply::error(__('messages.invalidRequest'));
        }

        if ($shipment->items->isEmpty()) {
            return Reply::error(__('messages.addItem'));
        }

        $shipment->status = 'confirmed';
        $shipment->updated_by = user()->id;
        $shipment->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    public function ship($id, SalesShipmentStockService $stockService)
    {
        abort_403(user()->permission('ship_sales_shipment') === 'none');
        $shipment = $this->queryByCompany()->with('items')->findOrFail($id);

        if (! in_array($shipment->status, ['confirmed', 'draft'], true)) {
            return Reply::error(__('messages.invalidRequest'));
        }

        if ($shipment->items->sum('quantity_shipped') <= 0) {
            return Reply::error(__('messages.quantityNumber'));
        }

        DB::transaction(function () use ($shipment, $stockService) {
            $shipment->status = 'shipped';
            $shipment->updated_by = user()->id;
            $shipment->save();

            $stockService->applyOutboundForShipment($shipment);
        });

        return Reply::success(__('messages.updateSuccess'));
    }

    public function deliver($id)
    {
        abort_403(user()->permission('ship_sales_shipment') === 'none');
        $shipment = $this->queryByCompany()->findOrFail($id);

        if ($shipment->status !== 'shipped') {
            return Reply::error(__('messages.invalidRequest'));
        }

        $shipment->status = 'delivered';
        $shipment->updated_by = user()->id;
        $shipment->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    public function reverse($id, SalesShipmentStockService $stockService)
    {
        abort_403(user()->permission('cancel_sales_shipment') === 'none');
        $shipment = $this->queryByCompany()->findOrFail($id);

        if (! in_array($shipment->status, ['shipped', 'delivered'], true)) {
            return Reply::error(__('messages.invalidRequest'));
        }

        DB::transaction(function () use ($shipment, $stockService) {
            $stockService->reverseOutboundForShipment($shipment);
            $shipment->status = 'confirmed';
            $shipment->updated_by = user()->id;
            $shipment->save();
        });

        return Reply::success(__('messages.updateSuccess'));
    }

    public function cancel($id, SalesShipmentStockService $stockService)
    {
        abort_403(user()->permission('cancel_sales_shipment') === 'none');
        $shipment = $this->queryByCompany()->findOrFail($id);

        if ($shipment->status === 'cancelled') {
            return Reply::success(__('messages.updateSuccess'));
        }

        DB::transaction(function () use ($shipment, $stockService) {
            if ($shipment->outbound_stock_applied) {
                $stockService->reverseOutboundForShipment($shipment);
            }

            $shipment->status = 'cancelled';
            $shipment->updated_by = user()->id;
            $shipment->save();
        });

        return Reply::success(__('messages.updateSuccess'));
    }

    protected function validateForm(Request $request, ?int $shipmentId = null): array
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|min:1',
            'warehouse_id' => 'required|integer|min:1',
            'shipment_number' => [
                'nullable',
                'string',
                'max:64',
                Rule::unique('sales_shipments', 'shipment_number')
                    ->where(fn($q) => $q->where('company_id', $this->company?->id))
                    ->ignore($shipmentId),
            ],
            'shipment_date' => 'required|string',
            'status' => 'required|in:draft,confirmed,shipped,delivered,cancelled',
            'notes' => 'nullable|string',
            'order_item_id' => 'required|array|min:1',
            'order_item_id.*' => 'required|integer|min:1',
            'product_id' => 'required|array|min:1',
            'product_id.*' => 'nullable|integer|min:1',
            'unit_id' => 'nullable|array',
            'unit_id.*' => 'nullable|integer|min:1',
            'quantity_ordered' => 'required|array|min:1',
            'quantity_ordered.*' => 'required|numeric|min:0',
            'quantity_shipped' => 'required|array|min:1',
            'quantity_shipped.*' => 'required|numeric|min:0',
            'batch_number' => 'nullable|array',
            'batch_number.*' => 'nullable|string|max:191',
        ]);

        $order = Order::query()
            ->where('company_id', $this->company?->id)
            ->with('items')
            ->findOrFail((int) $validated['order_id']);

        $remaining = $this->remainingQtyByOrderItem($order->id, $shipmentId);

        foreach ($validated['order_item_id'] as $idx => $orderItemId) {
            $orderItem = $order->items->firstWhere('id', (int) $orderItemId);
            if (! $orderItem) {
                abort(422, __('messages.invalidRequest'));
            }

            $requestQty = (float) ($validated['quantity_shipped'][$idx] ?? 0);
            $left = (float) ($remaining[$orderItemId] ?? 0);
            if ($requestQty > $left) {
                abort(422, __('messages.quantityNumber'));
            }

            // Shippable line must map to a product so stock movement can be posted correctly.
            $lineProductId = $validated['product_id'][$idx] ?? null;
            if ($requestQty > 0 && empty($lineProductId)) {
                abort(422, __('messages.invalidRequest'));
            }
        }

        if ($shipmentId) {
            $shipment = $this->queryByCompany()->findOrFail($shipmentId);
            if (in_array($shipment->status, ['shipped', 'delivered'], true)) {
                abort(422, __('messages.invalidRequest'));
            }
        }

        $validated['shipment_date'] = $this->parseCompanyDate($validated['shipment_date']);

        return $validated;
    }

    protected function upsertItems(SalesShipment $shipment, array $payload): void
    {
        SalesShipmentItem::query()->where('sales_shipment_id', $shipment->id)->delete();

        foreach ($payload['order_item_id'] as $idx => $orderItemId) {
            SalesShipmentItem::create([
                'sales_shipment_id' => $shipment->id,
                'order_item_id' => (int) $orderItemId,
                'product_id' => isset($payload['product_id'][$idx]) ? (int) $payload['product_id'][$idx] : null,
                'quantity_ordered' => (float) ($payload['quantity_ordered'][$idx] ?? 0),
                'quantity_shipped' => (float) ($payload['quantity_shipped'][$idx] ?? 0),
                'unit_id' => isset($payload['unit_id'][$idx]) ? (int) $payload['unit_id'][$idx] : null,
                'batch_number' => $payload['batch_number'][$idx] ?? null,
            ]);
        }
    }

    protected function remainingQtyByOrderItem(int $orderId, ?int $excludeShipmentId = null): array
    {
        $ordered = OrderItems::query()
            ->where('order_id', $orderId)
            ->pluck('quantity', 'id')
            ->map(fn($qty) => (float) $qty)
            ->toArray();

        $alreadyShipped = SalesShipmentItem::query()
            ->selectRaw('sales_shipment_items.order_item_id, SUM(sales_shipment_items.quantity_shipped) as shipped_qty')
            ->join('sales_shipments', 'sales_shipments.id', '=', 'sales_shipment_items.sales_shipment_id')
            ->where('sales_shipments.order_id', $orderId)
            ->whereNotIn('sales_shipments.status', ['cancelled'])
            ->when($excludeShipmentId, fn($q) => $q->where('sales_shipments.id', '!=', $excludeShipmentId))
            ->groupBy('sales_shipment_items.order_item_id')
            ->pluck('shipped_qty', 'order_item_id')
            ->map(fn($qty) => (float) $qty)
            ->toArray();

        $remaining = [];
        foreach ($ordered as $orderItemId => $orderedQty) {
            $remaining[$orderItemId] = max(0, $orderedQty - (float) ($alreadyShipped[$orderItemId] ?? 0));
        }

        return $remaining;
    }

    protected function nextShipmentNumber(): string
    {
        $lastId = SalesShipment::query()
            ->where('company_id', $this->company?->id)
            ->max('id');

        return 'SS-' . str_pad((string) ((int) $lastId + 1), 6, '0', STR_PAD_LEFT);
    }

    protected function parseCompanyDate(string $value): string
    {
        $value = trim($value);
        $format = company()?->date_format;

        if ($format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Throwable $e) {
                // fallback parse below
            }
        }

        return Carbon::parse($value)->format('Y-m-d');
    }

    protected function warehouseList()
    {
        if (! $this->company || ! class_exists(\Modules\Warehouse\Entities\Warehouse::class)) {
            return collect();
        }

        return \Modules\Warehouse\Entities\Warehouse::query()
            ->where('company_id', $this->company->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    protected function resolveOrderDefaultWarehouseId(Order $order): ?int
    {
        $candidate = (int) ($order->clientdetails?->default_warehouse_id ?? 0);
        if ($candidate <= 0 || ! class_exists(\Modules\Warehouse\Entities\Warehouse::class)) {
            return null;
        }

        $exists = \Modules\Warehouse\Entities\Warehouse::query()
            ->where('id', $candidate)
            ->where('company_id', $this->company?->id)
            ->where('status', 'active')
            ->exists();

        return $exists ? $candidate : null;
    }

    protected function queryByCompany()
    {
        $q = SalesShipment::query();

        if ($this->company) {
            $q->where('company_id', $this->company->id);
        }

        return $q;
    }
}
