<?php

namespace Modules\Warehouse\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Http\Controllers\Concerns\HandlesWarehouseErrors;
use Modules\Warehouse\Services\WarehouseQueryService;

class WarehouseMovementController extends AccountBaseController
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

    public function index(Request $request, WarehouseQueryService $queries)
    {
        abort_if(user()->permission('view_warehouse_stock') === 'none', 403, __('warehouse::app.err_permission_denied'));

        if (! $this->warehouseCompanyId()) {
            return redirect()->route('warehouse.index')->with('error', __('warehouse::app.err_company_context_missing'));
        }

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
        $warehouseTable = (new Warehouse)->getTable();
        $warehouses = Warehouse::where('status', 'active')
            ->when(Schema::hasColumn($warehouseTable, 'sort_order'), function ($query) {
                $query->orderBy('sort_order')->orderBy('name');
            }, function ($query) {
                $query->orderBy('name');
            })
            ->get();

        $this->pageTitle = 'warehouse::app.stockMovements';
        $this->pageIcon = 'ti-exchange-vertical';
        $this->movements = $movements;
        $this->warehouses = $warehouses;
        $this->warehouseId = $filters['warehouse_id'];
        $this->movementType = $filters['movement_type'];

        return view('warehouse::movements.index', $this->data);
    }
}
