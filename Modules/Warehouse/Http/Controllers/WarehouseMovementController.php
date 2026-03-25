<?php

namespace Modules\Warehouse\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Services\WarehouseQueryService;

class WarehouseMovementController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            abort_403(! in_array('warehouse', user_modules()));

            return $next($request);
        });
    }

    public function index(Request $request, WarehouseQueryService $queries)
    {
        abort_403(user()->permission('view_warehouse_stock') === 'none');

        $request->validate([
            'warehouse_id' => 'nullable|integer',
            'movement_type' => 'nullable|in:inbound,outbound',
            'search' => 'nullable|string|max:255',
        ]);

        $filters = [
            'warehouse_id' => $request->get('warehouse_id'),
            'movement_type' => $request->get('movement_type'),
            'search' => $request->get('search'),
        ];

        $movements = $queries->paginateStockMovements($filters, 25);
        $warehouses = Warehouse::where('status', 'active')->orderBy('name')->get();

        $this->pageTitle = 'warehouse::app.stockMovements';
        $this->pageIcon = 'ti-exchange-vertical';
        $this->movements = $movements;
        $this->warehouses = $warehouses;
        $this->warehouseId = $filters['warehouse_id'];
        $this->movementType = $filters['movement_type'];

        return view('warehouse::movements.index', $this->data);
    }
}
