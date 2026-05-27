<?php

namespace Modules\Production\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\Order;
use App\Models\Product;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Production\DataTables\ProductionOrdersDataTable;
use Modules\Production\Entities\ProductionBatch;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Entities\ProductionOrder;
use Modules\Production\Http\Concerns\HandlesProductionErrors;
use Modules\Production\Http\Requests\StoreProductionOrderRequest;
use Modules\Production\Http\Requests\UpdateProductionOrderRequest;
use Modules\Production\Services\ProductionBatchPlannedLinesApplicator;
use Modules\Production\Services\ProductionOrderMaterialRequirementsSummary;
use Modules\Production\Services\ProductionOrderSalesOrderPrefill;
use Modules\Production\Services\ProductionPostingService;
use Modules\Production\Support\ProductionBomFirstPolicy;
use Modules\Production\Support\ProductionProductUnitLabelMap;
use Modules\Production\Support\ProductionTenantAccess;
use Modules\Purchase\Entities\PurchaseManagementSetting;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseProductStock;

class ProductionOrderController extends AccountBaseController
{
    use HandlesProductionErrors;

    public function __construct(
        protected ProductionPostingService $posting,
        protected ProductionOrderMaterialRequirementsSummary $materialRequirementsSummary,
    ) {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            abort_if(! ProductionTenantAccess::tenantMayUseProduction(), 403);

            return $next($request);
        });
    }

    public function index(ProductionOrdersDataTable $dataTable)
    {
        $this->assertViewProductionOrders();

        $this->pageTitle = __('production::app.menuProductionOrders');

        return $dataTable->render('production::orders.index', $this->data);
    }

    public function create(Request $request): View|array
    {
        $this->assertAddProductionOrders();

        $this->pageTitle = __('production::app.newOrder');
        $prefillSalesOrderId = $request->integer('sales_order_id');
        $this->prefillSalesOrderId = $prefillSalesOrderId > 0 ? $prefillSalesOrderId : null;
        $this->salesOrderPrefill = $this->prefillSalesOrderId !== null
            ? app(ProductionOrderSalesOrderPrefill::class)->forSalesOrder($this->prefillSalesOrderId, (int) company()->id)
            : null;
        $this->addProductData(null, $this->prefillSalesOrderId);

        if ($request->ajax()) {
            $html = view('production::orders.ajax.create', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'production::orders.ajax.create';

        return view('production::orders.create', $this->data);
    }

    public function bomPreview(Request $request): JsonResponse|array
    {
        abort_unless(ProductionBomFirstPolicy::showBomPreviewOnOrderForm(), 404);

        $this->assertViewProductionOrders();

        $companyId = (int) company()->id;

        $validated = $request->validate([
            'production_bom_id' => [
                'required',
                'integer',
                Rule::exists('production_boms', 'id')->where(function ($query) use ($companyId): void {
                    $query->whereNull('company_id')->orWhere('company_id', $companyId);
                }),
            ],
            'planned_quantity' => ['required', 'numeric', 'min:0.0001'],
            'rm_warehouse_id' => [
                'nullable',
                'integer',
                Rule::exists('warehouses', 'id')->where('company_id', $companyId),
            ],
        ]);

        $plannedFg = (float) $validated['planned_quantity'];
        $rmWarehouseId = isset($validated['rm_warehouse_id']) && $validated['rm_warehouse_id'] !== ''
            ? (int) $validated['rm_warehouse_id']
            : null;

        $rows = $this->materialRequirementsSummary->previewForBom(
            $companyId,
            (int) $validated['production_bom_id'],
            $plannedFg,
            $rmWarehouseId,
        );

        $html = view('production::orders.partials.bom-preview-fragment', [
            'materialRequirements' => $rows,
            'materialRequirementsPlannedFg' => $plannedFg,
            'materialRequirementsHasShortfall' => $this->materialRequirementsSummary->hasShortfall($rows),
            'materialRequirementsShowStock' => $rmWarehouseId !== null && $rmWarehouseId > 0,
        ])->render();

        return Reply::dataOnly([
            'status' => 'success',
            'html' => $html,
            'lineCount' => count($rows),
        ]);
    }

    public function store(StoreProductionOrderRequest $request): RedirectResponse|JsonResponse
    {
        $order = ProductionOrder::query()->create(array_merge($request->validated(), [
            'company_id' => company()->id,
            'status' => ProductionOrder::STATUS_DRAFT,
        ]));

        if ($request->ajax()) {
            return response()->json(Reply::successWithData(__('messages.recordSaved'), [
                'redirectUrl' => $request->input('redirect_url') ?? $request->input('redirectUrl') ?? route('production.orders.index'),
                'orderId' => $order->id,
            ]));
        }

        return redirect()
            ->route('production.orders.show', $order)
            ->with('success', __('messages.recordSaved'));
    }

    public function show(ProductionOrder $order): View
    {
        $this->assertViewProductionOrders();
        $this->assertOrderInCompany($order);

        $this->pageTitle = __('production::app.orderDetail');
        $order->load([
            'batches.consumptions.warehouseProductBatch',
            'batches.outputs',
            'outputProduct',
            'bom',
            'rmWarehouse',
            'fgWarehouse',
            'salesOrder',
            'project',
            'bomSnapshotItems.componentProduct.unit',
            'bomSnapshotItems.unit',
        ]);
        $this->order = $order;
        $this->showBomSnapshotShadowColumn = (bool) config('production.phase2.yield_uom_shadow_enabled', false);

        $fgUnitMap = $order->outputProduct !== null
            ? ProductionProductUnitLabelMap::forProducts(collect([$order->outputProduct]), (int) company()->id)
            : collect();
        $this->orderFgUnitType = (string) ($fgUnitMap->get((string) $order->output_product_id) ?? $fgUnitMap->get($order->output_product_id) ?? '—');

        $this->materialRequirements = $this->materialRequirementsSummary->forOrder($order);
        $this->materialRequirementsPlannedFg = (float) $order->planned_quantity;
        $this->materialRequirementsHasShortfall = $this->materialRequirementsSummary->hasShortfall($this->materialRequirements);
        $this->materialRequirementsShowStock = in_array('warehouse', user_modules() ?: [], true)
            && class_exists(WarehouseProductStock::class);
        $this->canSuggestPurchaseOrder = $this->materialRequirementsHasShortfall
            && $this->materialRequirementsShowStock
            && in_array(user()->permission('add_purchase_order'), ['all', 'added', 'owned', 'both'], true)
            && in_array(PurchaseManagementSetting::MODULE_NAME, user_modules() ?: [], true);
        $this->purchaseOrderCreateUrl = $this->canSuggestPurchaseOrder ? route('purchase-order.create') : null;

        return view('production::orders.show', $this->data);
    }

    public function edit(ProductionOrder $order): View|array
    {
        $this->assertEditProductionOrders();
        $this->assertOrderInCompany($order);
        abort_if($order->status !== ProductionOrder::STATUS_DRAFT, 403);

        $this->pageTitle = __('production::app.saveDraft');
        $this->order = $order;
        $this->addProductData($order);

        if (request()->ajax()) {
            $html = view('production::orders.ajax.edit', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'production::orders.ajax.edit';

        return view('production::orders.edit', $this->data);
    }

    public function update(UpdateProductionOrderRequest $request, ProductionOrder $order): RedirectResponse|JsonResponse
    {
        $this->assertEditProductionOrders();
        $this->assertOrderInCompany($order);
        abort_if($order->status !== ProductionOrder::STATUS_DRAFT, 403);

        $order->update($request->validated());

        if ($request->ajax()) {
            return response()->json(Reply::successWithData(__('messages.updateSuccess'), [
                'redirectUrl' => $request->input('redirect_url') ?? $request->input('redirectUrl') ?? route('production.orders.index'),
                'orderId' => $order->id,
            ]));
        }

        return redirect()
            ->route('production.orders.show', $order)
            ->with('success', __('messages.updateSuccess'));
    }

    public function release(Request $request, ProductionOrder $order): RedirectResponse
    {
        $this->assertEditProductionOrders();
        $this->assertOrderInCompany($order);

        try {
            if ($order->status !== ProductionOrder::STATUS_DRAFT) {
                throw new \InvalidArgumentException(__('production::app.onlyDraftReleasable'));
            }

            $this->posting->releaseOrder($order);
            $order->refresh();

            if ($order->batches()->doesntExist()) {
                $batch = ProductionBatch::query()->create([
                    'company_id' => $order->company_id,
                    'production_order_id' => $order->id,
                    'batch_code' => 'PB-' . $order->id . '-' . strtoupper(substr(str_replace('.', '', uniqid('', true)), -8)),
                ]);

                app(ProductionBatchPlannedLinesApplicator::class)->autoApplyIfConfigured($batch->fresh(['order']));
            }
        } catch (\Throwable $e) {
            return $this->handleProductionThrowable($request, 'production_order_release', $e);
        }

        return back()->with('success', __('messages.updateSuccess'));
    }

    public function cancel(Request $request, ProductionOrder $order): RedirectResponse
    {
        $this->assertEditProductionOrders();
        $this->assertOrderInCompany($order);

        try {
            $this->posting->cancelOrder($order);
        } catch (\Throwable $e) {
            return $this->handleProductionThrowable($request, 'production_order_cancel', $e);
        }

        return back()->with('success', __('messages.updateSuccess'));
    }

    protected function addProductData(?ProductionOrder $draftOrderBeingEdited = null, ?int $prefillSalesOrderId = null): void
    {
        $companyId = (int) company()->id;

        $this->finishedGoods = Product::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->forBomOutput()
            ->orderBy('name')
            ->get(['id', 'name', 'sku']);

        $this->boms = ProductionBom::query()
            ->with(['outputProduct:id,name'])
            ->where(function ($q) use ($companyId): void {
                $q->where('company_id', $companyId)->orWhereNull('company_id');
            })
            ->orderByDesc('id')
            ->get();

        $this->warehouses = Warehouse::query()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $recentSalesOrders = Order::query()
            ->where('company_id', $companyId)
            ->eligibleForProductionOrderLink()
            ->orderByDesc('id')
            ->limit(250)
            ->get(['id', 'order_number', 'status']);

        $ensureSalesOrderInList = static function (?int $salesOrderId) use ($companyId, $recentSalesOrders) {
            if ($salesOrderId === null || $salesOrderId <= 0) {
                return $recentSalesOrders;
            }

            $linkedSalesOrder = Order::query()
                ->where('company_id', $companyId)
                ->whereKey($salesOrderId)
                ->first(['id', 'order_number', 'status']);

            if ($linkedSalesOrder === null) {
                return $recentSalesOrders;
            }

            if ($recentSalesOrders->contains(
                static fn(Order $row): bool => (int) $row->id === (int) $linkedSalesOrder->id
            )) {
                return $recentSalesOrders;
            }

            return $recentSalesOrders
                ->prepend($linkedSalesOrder)
                ->unique(static fn(Order $row): int => (int) $row->id)
                ->sortByDesc(static fn(Order $row): int => (int) $row->id)
                ->values();
        };

        if ($draftOrderBeingEdited !== null && $draftOrderBeingEdited->sales_order_id !== null) {
            $recentSalesOrders = $ensureSalesOrderInList((int) $draftOrderBeingEdited->sales_order_id);
        }

        if ($prefillSalesOrderId !== null) {
            $recentSalesOrders = $ensureSalesOrderInList($prefillSalesOrderId);
        }

        $this->recentSalesOrders = $recentSalesOrders;

        $this->projects = config('production.ui.show_linked_project_on_order_form')
            ? Project::query()
            ->where('company_id', $companyId)
            ->orderByDesc('id')
            ->limit(250)
            ->get(['id', 'project_name'])
            : collect();
    }

    protected function assertViewProductionOrders(): void
    {
        $p = user()->permission('view_production_orders');
        abort_if(! in_array($p, ['all', 'added', 'owned', 'both'], true), 403);
    }

    protected function assertAddProductionOrders(): void
    {
        $p = user()->permission('add_production_orders');
        abort_if(! in_array($p, ['all', 'added', 'owned', 'both'], true), 403);
    }

    protected function assertEditProductionOrders(): void
    {
        $p = user()->permission('edit_production_orders');
        abort_if(! in_array($p, ['all', 'added', 'owned', 'both'], true), 403);
    }

    protected function assertOrderInCompany(ProductionOrder $order): void
    {
        abort_if((int) $order->company_id !== (int) company()->id, 403);
    }
}
