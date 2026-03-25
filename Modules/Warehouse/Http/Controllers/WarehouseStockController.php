<?php

namespace Modules\Warehouse\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseProductStock;
use Modules\Warehouse\Services\StockMovementService;

class WarehouseStockController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            abort_403(! in_array('warehouse', user_modules()));

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $warehouseId = $request->get('warehouse_id');
        $search = $request->get('search');

        $warehouses = Warehouse::where('status', 'active')->get();

        $stocks = WarehouseProductStock::with(['product', 'warehouse'])
            ->when($warehouseId, function ($query) use ($warehouseId) {
                return $query->where('warehouse_id', $warehouseId);
            })
            ->when($search, function ($query) use ($search) {
                return $query->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%');
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
        $warehouses = Warehouse::where('status', 'active')->get();
        // Assuming products are global
        $products = Product::select('id', 'name', 'sku')->get();

        $this->pageTitle = 'warehouse::app.addStock';
        $this->pageIcon = 'ti-layout';
        $this->warehouses = $warehouses;
        $this->products = $products;

        return view('warehouse::stock.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:inbound,outbound,adjustment',
            'quantity' => 'required|numeric|min:0.01',
            'action' => 'nullable|in:add,remove',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            $service = app(StockMovementService::class);
            $companyId = auth()->user()->company_id ?? null;
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
                'reference_id' => auth()->id(),
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

            return redirect()->route('warehouse.stock.index')->with('success', 'Stock updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Stock Adjustment Error: '.$e->getMessage());

            return back()->with('error', 'Something went wrong! '.$e->getMessage());
        }
    }

    public function show($id)
    {
        // History of product in warehouse
    }
}
