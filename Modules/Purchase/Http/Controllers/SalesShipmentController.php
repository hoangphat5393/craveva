<?php

namespace Modules\Purchase\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Purchase\DataTables\SalesShipmentDataTable;
use Modules\Purchase\Services\SalesDoService;
use Modules\Purchase\Support\FlowPermission;
use Modules\Purchase\Support\SalesDoRuntime;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseProductBatch;

class SalesShipmentController extends AccountBaseController
{
    private function salesDoRouteName(string $action): string
    {
        $prefix = config('purchase.flow_naming_mode', 'compat_v2') === 'legacy' ? 'sales-shipments' : 'sales-do';

        return $prefix.'.'.$action;
    }

    private function salesDoTitleKey(): string
    {
        return config('purchase.flow_naming_mode', 'compat_v2') === 'legacy'
            ? 'purchase::app.menu.salesShipments'
            : 'purchase::app.menu.saleDeliveryOrder';
    }

    public function index(SalesShipmentDataTable $dataTable)
    {
        abort_403(! FlowPermission::allowsAlias('sales_do.view'));
        $this->pageTitle = __($this->salesDoTitleKey());

        return $dataTable->render('purchase::sales-shipment.index', $this->data);
    }

    public function create(Request $request)
    {
        abort_403(! FlowPermission::allowsAlias('sales_do.create'));
        $this->pageTitle = __('app.add').' '.__($this->salesDoTitleKey());
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
        abort_403(! FlowPermission::allowsAlias('sales_do.update'));
        $this->pageTitle = __('app.edit').' '.__($this->salesDoTitleKey());
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
        abort_403(! FlowPermission::allowsAlias('sales_do.view'));
        $this->shipment = $this->queryByCompany()
            ->with(['items.orderItem', 'items.warehouseBatch', 'order', 'warehouse'])
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
        abort_403(! FlowPermission::allowsAlias('sales_do.create') && ! FlowPermission::allowsAlias('sales_do.update'));
        $orderId = (int) $request->get('order_id');
        $shipmentId = (int) $request->get('shipment_id', 0);

        $order = Order::query()
            ->where('company_id', $this->company?->id)
            ->with(['items.unit', 'items.product', 'clientdetails'])
            ->findOrFail($orderId);

        $this->shipment = $shipmentId > 0 ? $this->queryByCompany()->with('items')->find($shipmentId) : null;
        $this->order = $order;
        $this->remainingByItem = $this->remainingQtyByOrderItem($order->id, $shipmentId);
        $resolvedWarehouseId = $this->resolveItemsWarehouseId($order, (int) $request->get('warehouse_id', 0));
        $this->batchOptionsByOrderItem = $this->buildBatchOptionsByOrderItem($order, $resolvedWarehouseId, $this->shipment);

        $html = view('purchase::sales-shipment.ajax.items', $this->data)->render();

        return Reply::dataOnly([
            'status' => 'success',
            'html' => $html,
            'defaultWarehouseId' => $resolvedWarehouseId,
        ]);
    }

    public function store(Request $request)
    {
        abort_403(! FlowPermission::allowsAlias('sales_do.create'));
        $payload = $this->validateForm($request);
        $payload['shipment_number'] = $payload['shipment_number'] ?: $this->nextShipmentNumber();

        $shipment = app(SalesDoService::class)->create($payload, $this->company?->id, user()->id);

        return Reply::successWithData(__('messages.recordSaved'), [
            'redirectUrl' => route($this->salesDoRouteName('show'), $shipment->id),
        ]);
    }

    public function update(Request $request, $id)
    {
        abort_403(! FlowPermission::allowsAlias('sales_do.update'));
        $shipment = $this->queryByCompany()->with('items')->findOrFail($id);
        abort_if(in_array($shipment->status, ['shipped', 'delivered'], true), 403);

        $payload = $this->validateForm($request, $shipment->id);
        $payload['shipment_number'] = $payload['shipment_number'] ?: $shipment->shipment_number;

        app(SalesDoService::class)->update($shipment, $payload, user()->id);

        return Reply::successWithData(__('messages.updateSuccess'), [
            'redirectUrl' => route($this->salesDoRouteName('show'), $shipment->id),
        ]);
    }

    public function confirm($id, SalesDoService $salesDoService)
    {
        abort_403(! FlowPermission::allowsAlias('sales_do.update'));
        $shipment = $this->queryByCompany()->with('items')->findOrFail($id);

        $error = $salesDoService->confirm($shipment);
        if ($error) {
            return Reply::error(__($error));
        }

        return Reply::success(__('messages.updateSuccess'));
    }

    public function ship($id, SalesDoService $salesDoService)
    {
        abort_403(! FlowPermission::allowsAlias('sales_do.ship'));
        $shipment = $this->queryByCompany()->with('items')->findOrFail($id);

        $error = $salesDoService->ship($shipment);
        if ($error) {
            return Reply::error(__($error));
        }

        return Reply::success(__('messages.updateSuccess'));
    }

    public function deliver($id, SalesDoService $salesDoService)
    {
        abort_403(! FlowPermission::allowsAlias('sales_do.ship'));
        $shipment = $this->queryByCompany()->findOrFail($id);

        $error = $salesDoService->deliver($shipment);
        if ($error) {
            return Reply::error(__($error));
        }

        return Reply::success(__('messages.updateSuccess'));
    }

    public function reverse($id, SalesDoService $salesDoService)
    {
        abort_403(! FlowPermission::allowsAlias('sales_do.cancel'));
        $shipment = $this->queryByCompany()->findOrFail($id);

        $error = $salesDoService->reverse($shipment);
        if ($error) {
            return Reply::error(__($error));
        }

        return Reply::success(__('messages.updateSuccess'));
    }

    public function cancel($id, SalesDoService $salesDoService)
    {
        abort_403(! FlowPermission::allowsAlias('sales_do.cancel'));
        $shipment = $this->queryByCompany()->findOrFail($id);

        $error = $salesDoService->cancel($shipment);
        if ($error) {
            return Reply::error(__($error));
        }

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
                Rule::unique(SalesDoRuntime::headerTable(), SalesDoRuntime::numberColumn())
                    ->where(fn ($q) => $q->where('company_id', $this->company?->id))
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
            'warehouse_batch_id' => 'nullable|array',
            'warehouse_batch_id.*' => 'nullable|integer|min:1',
            'expiration_date' => 'nullable|array',
            'expiration_date.*' => 'nullable|date',
        ]);

        $order = Order::query()
            ->where('company_id', $this->company?->id)
            ->with('items')
            ->findOrFail((int) $validated['order_id']);

        $remaining = $this->remainingQtyByOrderItem($order->id, $shipmentId);

        $batchIds = collect($validated['warehouse_batch_id'] ?? [])
            ->filter(fn ($id) => ! empty($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $batchMap = collect();
        if ($batchIds->isNotEmpty()) {
            $batchMap = WarehouseProductBatch::query()
                ->where('company_id', $this->company?->id)
                ->whereIn('id', $batchIds->all())
                ->get()
                ->keyBy('id');
        }

        $selectedBatchIdsForValidation = collect();
        if ($shipmentId) {
            $existingShipment = $this->queryByCompany()->with('items')->find($shipmentId);
            if ($existingShipment && $existingShipment->relationLoaded('items')) {
                $selectedBatchIdsForValidation = $existingShipment->items
                    ->pluck('warehouse_batch_id')
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();
            }
        }

        $trackedProductIds = Product::query()
            ->whereIn('id', collect($validated['product_id'] ?? [])->filter()->map(fn ($id) => (int) $id)->unique()->values()->all())
            ->where('track_inventory', 1)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        foreach ($validated['order_item_id'] as $idx => $orderItemId) {
            $orderItem = $order->items->firstWhere('id', (int) $orderItemId);
            if (! $orderItem) {
                abort(422, __('messages.invalidRequest'));
            }

            $requestQty = (float) ($validated['quantity_shipped'][$idx] ?? 0);
            $left = (float) ($remaining[$orderItemId] ?? 0);
            if ($requestQty > $left) {
                abort(422, 'Ship quantity cannot exceed remaining quantity (remaining: '.number_format($left, 2, '.', '').', requested: '.number_format($requestQty, 2, '.', '').').');
            }

            // Shippable line must map to a product so stock movement can be posted correctly.
            $lineProductId = $validated['product_id'][$idx] ?? null;
            if ($requestQty > 0 && empty($lineProductId)) {
                abort(422, __('messages.invalidRequest'));
            }

            $batchId = (int) ($validated['warehouse_batch_id'][$idx] ?? 0);
            $requiresBatch = ! empty($lineProductId)
                && $trackedProductIds->contains((int) $lineProductId)
                && $this->productHasSelectableBatches(
                    (int) $validated['warehouse_id'],
                    (int) $lineProductId,
                    $selectedBatchIdsForValidation
                );
            if ($requestQty > 0 && $requiresBatch && $batchId <= 0) {
                abort(422, 'Please select a valid batch (batch + expiry) before shipping.');
            }

            if ($requestQty > 0 && $batchId > 0) {
                $batch = $batchMap->get($batchId);
                if (! $batch) {
                    abort(422, 'Selected batch was not found in current company.');
                }

                if ((int) $batch->warehouse_id !== (int) $validated['warehouse_id']) {
                    abort(422, 'Selected batch does not belong to the selected warehouse.');
                }

                if (! empty($lineProductId) && (int) $batch->product_id !== (int) $lineProductId) {
                    abort(422, 'Selected batch does not belong to the selected product.');
                }

                $availableQty = max(0, (float) $batch->quantity - (float) $batch->reserved_quantity);
                if ($requestQty > $availableQty) {
                    abort(422, 'Ship quantity cannot exceed selected batch available quantity (available: '.number_format($availableQty, 4, '.', '').', requested: '.number_format($requestQty, 4, '.', '').').');
                }

                $validated['batch_number'][$idx] = $batch->batch_number;
                $validated['expiration_date'][$idx] = $batch->expiration_date;
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

    protected function remainingQtyByOrderItem(int $orderId, ?int $excludeShipmentId = null): array
    {
        $ordered = OrderItems::query()
            ->where('order_id', $orderId)
            ->pluck('quantity', 'id')
            ->map(fn ($qty) => (float) $qty)
            ->toArray();

        $itemModelClass = SalesDoRuntime::itemModelClass();
        $headerTable = SalesDoRuntime::headerTable();
        $itemTable = SalesDoRuntime::itemTable();
        $itemForeignKey = SalesDoRuntime::itemForeignKey();

        $alreadyShipped = $itemModelClass::query()
            ->selectRaw($itemTable.'.order_item_id, SUM('.$itemTable.'.quantity_shipped) as shipped_qty')
            ->join($headerTable, $headerTable.'.id', '=', $itemTable.'.'.$itemForeignKey)
            ->where($headerTable.'.order_id', $orderId)
            ->whereNotIn($headerTable.'.status', ['cancelled'])
            ->when($excludeShipmentId, fn ($q) => $q->where($headerTable.'.id', '!=', $excludeShipmentId))
            ->groupBy($itemTable.'.order_item_id')
            ->pluck('shipped_qty', 'order_item_id')
            ->map(fn ($qty) => (float) $qty)
            ->toArray();

        $remaining = [];
        foreach ($ordered as $orderItemId => $orderedQty) {
            $remaining[$orderItemId] = max(0, $orderedQty - (float) ($alreadyShipped[$orderItemId] ?? 0));
        }

        return $remaining;
    }

    protected function nextShipmentNumber(): string
    {
        $headerModelClass = SalesDoRuntime::headerModelClass();
        $lastId = $headerModelClass::query()
            ->where('company_id', $this->company?->id)
            ->max('id');

        return 'SS-'.str_pad((string) ((int) $lastId + 1), 6, '0', STR_PAD_LEFT);
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
        if (! $this->company || ! class_exists(Warehouse::class)) {
            return collect();
        }

        return Warehouse::query()
            ->where('company_id', $this->company->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    protected function resolveOrderDefaultWarehouseId(Order $order): ?int
    {
        $candidate = (int) ($order->clientdetails?->default_warehouse_id ?? 0);
        if ($candidate <= 0 || ! class_exists(Warehouse::class)) {
            return null;
        }

        $exists = Warehouse::query()
            ->where('id', $candidate)
            ->where('company_id', $this->company?->id)
            ->where('status', 'active')
            ->exists();

        return $exists ? $candidate : null;
    }

    protected function resolveItemsWarehouseId(Order $order, int $requestedWarehouseId): ?int
    {
        if ($requestedWarehouseId > 0) {
            $exists = Warehouse::query()
                ->where('id', $requestedWarehouseId)
                ->where('company_id', $this->company?->id)
                ->where('status', 'active')
                ->exists();
            if ($exists) {
                return $requestedWarehouseId;
            }
        }

        return $this->resolveOrderDefaultWarehouseId($order);
    }

    protected function buildBatchOptionsByOrderItem(Order $order, ?int $warehouseId, $shipment = null): array
    {
        $orderItemIds = $order->items->pluck('id')->all();
        $result = array_fill_keys($orderItemIds, []);

        if (! $warehouseId) {
            return $result;
        }

        $productIds = $order->items
            ->pluck('product_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($productIds->isEmpty()) {
            return $result;
        }

        $trackedProductIds = Product::query()
            ->whereIn('id', $productIds->all())
            ->where('track_inventory', 1)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($trackedProductIds->isEmpty()) {
            return $result;
        }

        $selectedBatchIds = collect();
        if ($shipment && $shipment->relationLoaded('items')) {
            $selectedBatchIds = $shipment->items
                ->pluck('warehouse_batch_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();
        }

        $batches = WarehouseProductBatch::query()
            ->where('company_id', $this->company?->id)
            ->where('warehouse_id', $warehouseId)
            ->whereIn('product_id', $trackedProductIds->all())
            // Only expose selectable "real" batches (has batch no. or expiry identity).
            ->where(function ($q) {
                $q->whereNotNull('batch_number')
                    ->orWhereNotNull('expiration_date');
            })
            ->where(function ($q) use ($selectedBatchIds) {
                $q->whereRaw('(quantity - reserved_quantity) > 0');
                if ($selectedBatchIds->isNotEmpty()) {
                    $q->orWhereIn('id', $selectedBatchIds->all());
                }
            })
            ->orderByRaw('expiration_date IS NULL, expiration_date ASC')
            ->orderBy('id')
            ->get()
            ->groupBy('product_id');

        foreach ($order->items as $item) {
            $productId = (int) ($item->product_id ?? 0);
            if ($productId <= 0) {
                continue;
            }

            $rows = $batches->get($productId, collect())->map(function ($batch) {
                $expiry = $batch->expiration_date ? Carbon::parse($batch->expiration_date)->format('Y-m-d') : null;
                $available = max(0, (float) $batch->quantity - (float) $batch->reserved_quantity);

                return [
                    'id' => (int) $batch->id,
                    'batch_number' => $batch->batch_number,
                    'expiration_date' => $expiry,
                    'available_quantity' => $available,
                ];
            })->values()->all();

            $result[(int) $item->id] = $rows;
        }

        return $result;
    }

    /**
     * Matches batch dropdown logic: a line needs batch selection only when at least one
     * warehouse batch row exists with available qty, or (when editing) the DO already references a batch row.
     */
    protected function productHasSelectableBatches(int $warehouseId, int $productId, $selectedBatchIds): bool
    {
        if ($warehouseId <= 0 || $productId <= 0) {
            return false;
        }

        $selected = collect($selectedBatchIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        return WarehouseProductBatch::query()
            ->where('company_id', $this->company?->id)
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->where(function ($q) {
                $q->whereNotNull('batch_number')
                    ->orWhereNotNull('expiration_date');
            })
            ->where(function ($q) use ($selected) {
                $q->whereRaw('(quantity - reserved_quantity) > 0');
                if ($selected->isNotEmpty()) {
                    $q->orWhereIn('id', $selected->all());
                }
            })
            ->exists();
    }

    protected function queryByCompany()
    {
        $headerModelClass = SalesDoRuntime::headerModelClass();
        $q = $headerModelClass::query();

        if ($this->company) {
            $q->where('company_id', $this->company->id);
        }

        return $q;
    }
}
