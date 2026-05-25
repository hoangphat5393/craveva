<?php

namespace Modules\Warehouse\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Modules\Warehouse\DataTables\WarehouseStockDataTable;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseProductBatch;
use Modules\Warehouse\Entities\WarehouseProductStock;
use Modules\Warehouse\Http\Controllers\Concerns\HandlesWarehouseErrors;
use Modules\Warehouse\Services\StockMovementService;
use Modules\Warehouse\Services\WarehouseFlowPolicyService;
use Modules\Warehouse\Services\WarehouseReconciliationService;

class WarehouseStockController extends AccountBaseController
{
    use HandlesWarehouseErrors;

    public function __construct(
        protected WarehouseFlowPolicyService $flowPolicyService
    ) {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            abort_if(! in_array('warehouse', user_modules()), 403, __('warehouse::app.err_module_not_warehouse'));

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, WarehouseStockDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_warehouse_stock');
        abort_if($viewPermission === 'none', 403, __('warehouse::app.err_permission_denied'));

        $warehouseTable = (new Warehouse)->getTable();
        $warehouses = Warehouse::where('status', 'active')
            ->when(Schema::hasColumn($warehouseTable, 'sort_order'), function ($query) {
                $query->orderBy('sort_order')->orderBy('name');
            }, function ($query) {
                $query->orderBy('name');
            })
            ->get();

        $companyId = $this->warehouseCompanyId();
        $this->inventoryReconciliationWidget = null;
        if ($companyId && Schema::hasTable('warehouse_product_batches') && Schema::hasTable('warehouse_product_stock')) {
            $this->inventoryReconciliationWidget = app(WarehouseReconciliationService::class)
                ->inventorySnapshotVsBatchTotals((int) $companyId);
        }

        $this->pageTitle = 'warehouse::app.adjustStock';
        $this->pageIcon = 'ti-layout';
        $this->warehouses = $warehouses;
        $this->showEmptyStockOnboarding = ! WarehouseProductStock::query()
            ->whereHas('product')
            ->whereHas('warehouse')
            ->exists();

        return $dataTable->render('warehouse::stock.index', $this->data);
    }

    /**
     * Show the form for creating a new resource (Stock Adjustment).
     */
    public function create()
    {
        $addPermission = user()->permission('add_warehouse_stock');
        abort_if(! in_array($addPermission, ['all', 'added'], true), 403, __('warehouse::app.err_permission_denied'));

        $warehouseTable = (new Warehouse)->getTable();
        $warehouses = Warehouse::where('status', 'active')
            ->when(Schema::hasColumn($warehouseTable, 'sort_order'), function ($query) {
                $query->orderBy('sort_order')->orderBy('name');
            }, function ($query) {
                $query->orderBy('name');
            })
            ->get();
        $products = Product::select('id', 'name', 'sku')->get();

        $this->pageTitle = 'warehouse::app.adjustStockAction';
        $this->pageIcon = 'ti-layout';
        $this->warehouses = $warehouses;
        $this->products = $products;

        if (request()->ajax()) {
            $html = view('warehouse::stock.ajax.create', $this->data)->render();

            return response()->json(Reply::dataOnly([
                'status' => 'success',
                'html' => $html,
                'title' => __('warehouse::app.adjustStockAction'),
            ]));
        }

        return view('warehouse::stock.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $addPermission = user()->permission('add_warehouse_stock');
        abort_if(! in_array($addPermission, ['all', 'added'], true), 403, __('warehouse::app.err_permission_denied'));

        $companyId = $this->warehouseCompanyId();
        if (! $companyId) {
            return $this->warehouseFailResponse($request, __('warehouse::app.err_company_context_missing'));
        }

        $request->validate([
            'warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')->where('company_id', $companyId)],
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('company_id', $companyId)],
            'type' => 'required|in:inbound,outbound,adjustment',
            'quantity' => 'required|numeric|min:0.01',
            'action' => 'nullable|in:add,remove',
            'reason' => 'nullable|string|max:255',
        ], [
            'warehouse_id.required' => __('warehouse::app.validation_warehouse_required'),
            'warehouse_id.exists' => __('warehouse::app.validation_warehouse_invalid_company'),
            'product_id.required' => __('warehouse::app.validation_product_required'),
            'product_id.exists' => __('warehouse::app.validation_product_invalid_company'),
            'type.required' => __('warehouse::app.validation_adjustment_type_required'),
            'type.in' => __('warehouse::app.validation_adjustment_type_invalid'),
            'quantity.required' => __('warehouse::app.validation_quantity_required'),
            'quantity.numeric' => __('warehouse::app.validation_quantity_numeric'),
            'quantity.min' => __('warehouse::app.validation_quantity_positive'),
            'action.in' => __('warehouse::app.validation_stock_action_invalid'),
            'reason.max' => __('warehouse::app.validation_reason_max'),
        ], [
            'warehouse_id' => __('warehouse'),
            'product_id' => __('product'),
            'type' => __('warehouse::app.adjustmentType'),
            'quantity' => __('quantity'),
            'action' => __('stock action'),
            'reason' => __('reason'),
        ]);

        try {
            $service = app(StockMovementService::class);
            $warehouseId = (int) $request->warehouse_id;
            $productId = (int) $request->product_id;
            $quantity = (float) $request->quantity;

            $payloadBase = [
                'company_id' => $companyId,
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'batch_number' => null,
                'expiry_date' => null,
                'reference_type' => 'manual_warehouse_stock',
                'reference_id' => user()?->id,
            ];

            if ($request->type === 'inbound') {
                $service->recordInbound($payloadBase);
            } elseif ($request->type === 'outbound') {
                $service->recordOutbound($payloadBase);
            } else {
                $action = $request->input('action', 'add');
                if ($action === 'add') {
                    $service->recordInbound($payloadBase);
                } else {
                    $service->recordOutbound($payloadBase);
                }
            }

            if ($request->ajax()) {
                session()->flash('success', __('warehouse::app.success_stock_adjustment_saved'));

                return response()->json(Reply::redirect(route('warehouse.stock.index')));
            }

            return redirect()->route('warehouse.stock.index')->with('success', __('warehouse::app.success_stock_adjustment_saved'));
        } catch (\Throwable $e) {
            return $this->handleWarehouseThrowable($request, 'Warehouse stock store', $e, $request->except(['_token']));
        }
    }

    public function show($id)
    {
        // History of product in warehouse
    }

    protected function appendSellableMetrics($stocks): void
    {
        $warehouseIds = $stocks->pluck('warehouse_id')->unique()->values()->all();
        $productIds = $stocks->pluck('product_id')->unique()->values()->all();

        if ($warehouseIds === [] || $productIds === []) {
            return;
        }

        $batchAgg = WarehouseProductBatch::query()
            ->selectRaw('warehouse_id, product_id, SUM(reserved_quantity) as reserved')
            ->whereIn('warehouse_id', $warehouseIds)
            ->whereIn('product_id', $productIds)
            ->groupBy('warehouse_id', 'product_id')
            ->get()
            ->keyBy(fn ($row) => $row->warehouse_id.':'.$row->product_id);

        $stocks->getCollection()->transform(function ($stock) use ($batchAgg) {
            $key = $stock->warehouse_id.':'.$stock->product_id;
            $reserved = (float) ($batchAgg->get($key)->reserved ?? 0);
            $onHand = (float) $stock->quantity;
            $available = max(0.0, $onHand - $reserved);
            $warehouseType = (string) ($stock->warehouse->warehouse_type ?? 'normal');
            $sellable = $this->flowPolicyService->isSellableWarehouseType($warehouseType) ? $available : 0.0;

            $stock->reserved_quantity = $reserved;
            $stock->available_quantity = $available;
            $stock->sellable_quantity = $sellable;
            $stock->warehouse_type = $warehouseType;

            return $stock;
        });
    }
}
