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
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseProductStock;
use Modules\Warehouse\Http\Controllers\Concerns\HandlesWarehouseErrors;
use Modules\Warehouse\Services\StockMovementService;

class WarehouseStockController extends AccountBaseController
{
    use HandlesWarehouseErrors;

    public function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            abort_if(! in_array('warehouse', user_modules()), 403, __('warehouse::app.err_module_not_warehouse'));

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $viewPermission = user()->permission('view_warehouse_stock');
        abort_if($viewPermission === 'none', 403, __('warehouse::app.err_permission_denied'));

        $warehouseId = $request->get('warehouse_id');
        $search = $request->get('search');

        $warehouseTable = (new Warehouse)->getTable();
        $warehouses = Warehouse::where('status', 'active')
            ->when(Schema::hasColumn($warehouseTable, 'sort_order'), function ($query) {
                $query->orderBy('sort_order')->orderBy('name');
            }, function ($query) {
                $query->orderBy('name');
            })
            ->get();

        $stocks = WarehouseProductStock::with(['product', 'warehouse'])
            ->when($warehouseId, function ($query) use ($warehouseId) {
                return $query->where('warehouse_id', $warehouseId);
            })
            ->when($search, function ($query) use ($search) {
                return $query->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('sku', 'like', '%' . $search . '%');
                });
            })
            ->paginate(20);

        $this->pageTitle = 'warehouse::app.adjustStock';
        $this->pageIcon = 'ti-layout';
        $this->stocks = $stocks;
        $this->warehouses = $warehouses;
        $this->warehouseId = $warehouseId;

        return view('warehouse::stock.index', $this->data);
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

        $this->pageTitle = 'warehouse::app.addStock';
        $this->pageIcon = 'ti-layout';
        $this->warehouses = $warehouses;
        $this->products = $products;

        if (request()->ajax()) {
            $html = view('warehouse::stock.ajax.create', $this->data)->render();

            return response()->json(Reply::dataOnly([
                'status' => 'success',
                'html' => $html,
                'title' => __('warehouse::app.addStock'),
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
                session()->flash('success', __('messages.recordSaved'));

                return response()->json(Reply::redirect(route('warehouse.stock.index')));
            }

            return redirect()->route('warehouse.stock.index')->with('success', __('messages.recordSaved'));
        } catch (\Throwable $e) {
            return $this->handleWarehouseThrowable($request, 'Warehouse stock store', $e, $request->except(['_token']));
        }
    }

    public function show($id)
    {
        // History of product in warehouse
    }
}
