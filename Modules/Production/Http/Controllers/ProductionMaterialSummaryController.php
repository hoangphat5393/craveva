<?php

declare(strict_types=1);

namespace Modules\Production\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Production\DataTables\ProductionMaterialShortagesDataTable;
use Modules\Production\Services\ProductionMaterialSummaryService;
use Modules\Production\Support\ProductionTenantAccess;
use Modules\Warehouse\Entities\Warehouse;

class ProductionMaterialSummaryController extends AccountBaseController
{
    public function __construct(
        private readonly ProductionMaterialSummaryService $materialSummaryService,
    ) {
        parent::__construct();

        $this->middleware(function ($request, $next) {
            abort_if(! ProductionTenantAccess::tenantMayUseProduction(), 403);

            return $next($request);
        });
    }

    public function index(ProductionMaterialShortagesDataTable $dataTable, Request $request)
    {
        $this->assertViewProductionOrders();

        $companyId = (int) company()->id;
        $this->pageTitle = __('production::app.materialShortageSummary');
        $this->statusScope = $this->materialSummaryService->normalizeStatusScope((string) $request->input('status_scope'));
        $this->onlyShortage = $request->has('only_shortage') ? $request->boolean('only_shortage') : true;
        // Warehouse filter hidden on UI (see material-shortages/index.blade.php). Re-enable when needed:
        // $this->warehouseOptions = Warehouse::query()
        //     ->where('company_id', $companyId)
        //     ->where('status', 'active')
        //     ->orderBy('name')
        //     ->get(['id', 'name']);
        $this->warehouseOptions = collect();
        $this->materialOptions = Product::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->forBomComponents()
            ->orderBy('name')
            ->get(['id', 'name']);

        return $dataTable->render('production::material-shortages.index', $this->data);
    }

    public function orders(Request $request): View
    {
        $this->assertViewProductionOrders();

        $companyId = (int) company()->id;
        $materialId = (int) $request->integer('material_id');
        $warehouseId = (int) $request->integer('warehouse_id');

        abort_if($materialId <= 0 || $warehouseId <= 0, 404);

        $material = Product::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->findOrFail($materialId, ['id', 'name']);

        $warehouse = Warehouse::query()
            ->where('company_id', $companyId)
            ->findOrFail($warehouseId, ['id', 'name']);

        $filters = [
            'status_scope' => $request->input('status_scope'),
            'warehouse_id' => $warehouseId,
            'material_id' => $materialId,
            'only_shortage' => false,
        ];

        $this->pageTitle = __('production::app.materialShortageOrdersPageTitle');
        $this->material = $material;
        $this->warehouse = $warehouse;
        $this->statusScope = $this->materialSummaryService->normalizeStatusScope((string) $request->input('status_scope'));
        $this->summary = $this->materialSummaryService->summaryForMaterial($companyId, $materialId, $warehouseId, $filters);
        $this->orderRows = $this->materialSummaryService->detailForMaterial($companyId, $materialId, $warehouseId, $filters);

        return view('production::material-shortages.orders', $this->data);
    }

    protected function assertViewProductionOrders(): void
    {
        $permission = user()->permission('view_production_orders');

        abort_if(! in_array($permission, ['all', 'added', 'owned', 'both'], true), 403);
    }
}
