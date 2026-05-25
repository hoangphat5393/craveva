<?php

namespace Modules\Warehouse\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Modules\Warehouse\DataTables\WarehouseMovementsDataTable;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Http\Controllers\Concerns\HandlesWarehouseErrors;

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

    public function index(Request $request, WarehouseMovementsDataTable $dataTable)
    {
        abort_if(user()->permission('view_warehouse_stock') === 'none', 403, __('warehouse::app.err_permission_denied'));

        if (! $this->warehouseCompanyId()) {
            return redirect()->route('warehouse.index')->with('error', __('warehouse::app.err_company_context_missing'));
        }

        $request->validate([
            'warehouse_id' => 'nullable|integer',
            'movement_type' => 'nullable|in:inbound,outbound',
            'searchText' => 'nullable|string|max:255',
        ]);

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
        $this->warehouses = $warehouses;

        return $dataTable->render('warehouse::movements.index', $this->data);
    }
}
