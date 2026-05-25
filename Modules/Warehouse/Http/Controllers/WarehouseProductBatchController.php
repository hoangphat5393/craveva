<?php

namespace Modules\Warehouse\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use App\Models\StockMovement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Modules\Production\Entities\ProductionBatch;
use Modules\Warehouse\DataTables\WarehouseProductBatchesDataTable;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseProductBatch;
use Modules\Warehouse\Http\Controllers\Concerns\HandlesWarehouseErrors;

class WarehouseProductBatchController extends AccountBaseController
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

    public function index(Request $request, WarehouseProductBatchesDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_warehouse_stock');
        abort_if($viewPermission === 'none', 403, __('warehouse::app.err_permission_denied'));

        $companyId = $this->warehouseCompanyId();
        abort_if(! $companyId, 403, __('warehouse::app.err_company_context_missing'));

        $warehouseTable = (new Warehouse)->getTable();
        $warehouses = Warehouse::where('status', 'active')
            ->where('company_id', $companyId)
            ->when(Schema::hasColumn($warehouseTable, 'sort_order'), function ($query) {
                $query->orderBy('sort_order')->orderBy('name');
            }, function ($query) {
                $query->orderBy('name');
            })
            ->get();

        $this->pageTitle = 'warehouse::app.stockBatches';
        $this->pageIcon = 'ti-layout';
        $this->warehouses = $warehouses;

        return $dataTable->render('warehouse::product-batches.index', $this->data);
    }

    public function show(WarehouseProductBatch $warehouseProductBatch): View
    {
        $viewPermission = user()->permission('view_warehouse_stock');
        abort_if($viewPermission === 'none', 403, __('warehouse::app.err_permission_denied'));

        $companyId = $this->warehouseCompanyId();
        abort_if(! $companyId, 403, __('warehouse::app.err_company_context_missing'));

        $warehouseProductBatch->load(['warehouse', 'product']);
        if ((int) $warehouseProductBatch->warehouse?->company_id !== (int) $companyId) {
            abort(404);
        }

        $movementsQuery = StockMovement::query()
            ->where('company_id', $companyId)
            ->where('product_id', $warehouseProductBatch->product_id)
            ->where(function ($q) use ($warehouseProductBatch): void {
                $wid = (int) $warehouseProductBatch->warehouse_id;
                $q->where('warehouse_to_id', $wid)->orWhere('warehouse_from_id', $wid);
            });

        $batchNo = $warehouseProductBatch->batch_number;
        if ($batchNo !== null && $batchNo !== '') {
            $movementsQuery->where('batch_number', $batchNo);
        } else {
            $movementsQuery->where(function ($q): void {
                $q->whereNull('batch_number')->orWhere('batch_number', '');
            });
        }

        if ($warehouseProductBatch->expiration_date !== null) {
            $movementsQuery->whereDate('expiry_date', $warehouseProductBatch->expiration_date);
        } else {
            $movementsQuery->whereNull('expiry_date');
        }

        $movements = $movementsQuery->orderByDesc('id')->limit(100)->get();

        $productionBatchReferenceType = ProductionBatch::class;

        $this->pageTitle = 'warehouse::app.warehouseBatchDetail';
        $this->pageIcon = 'ti-layout';
        $this->batch = $warehouseProductBatch;
        $this->movements = $movements;
        $this->productionBatchReferenceType = $productionBatchReferenceType;

        return view('warehouse::product-batches.show', $this->data);
    }
}
