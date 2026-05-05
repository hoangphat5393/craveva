<?php

namespace Modules\Production\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Production\Entities\ProductionBatch;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Entities\ProductionOrder;
use Modules\Production\Http\Concerns\HandlesProductionErrors;
use Modules\Production\Http\Requests\StoreProductionOrderRequest;
use Modules\Production\Http\Requests\UpdateProductionOrderRequest;
use Modules\Production\Services\ProductionPostingService;
use Modules\Production\Support\ProductionTenantAccess;
use Modules\Warehouse\Entities\Warehouse;

class ProductionOrderController extends AccountBaseController
{
    use HandlesProductionErrors;

    public function __construct(
        protected ProductionPostingService $posting
    ) {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            abort_if(! ProductionTenantAccess::tenantMayUseProduction(), 403);

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $this->assertViewProductionOrders();

        $this->pageTitle = __('production::app.menuProductionOrders');

        $query = ProductionOrder::query()->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $this->orders = $query->paginate(25)->withQueryString();

        return view('production::orders.index', $this->data);
    }

    public function create(): View
    {
        $this->assertAddProductionOrders();

        $this->pageTitle = __('production::app.newOrder');
        $this->addProductData();

        return view('production::orders.create', $this->data);
    }

    public function store(StoreProductionOrderRequest $request): RedirectResponse
    {
        $order = ProductionOrder::query()->create(array_merge($request->validated(), [
            'company_id' => company()->id,
            'status' => ProductionOrder::STATUS_DRAFT,
        ]));

        return redirect()
            ->route('production.orders.show', $order)
            ->with('success', __('messages.recordSaved'));
    }

    public function show(ProductionOrder $order): View
    {
        $this->assertViewProductionOrders();
        $this->assertOrderInCompany($order);

        $this->pageTitle = __('production::app.orderDetail');
        $order->load(['batches.consumptions.warehouseProductBatch', 'batches.outputs', 'outputProduct', 'bom', 'rmWarehouse', 'fgWarehouse']);
        $this->order = $order;

        return view('production::orders.show', $this->data);
    }

    public function edit(ProductionOrder $order): View
    {
        $this->assertEditProductionOrders();
        $this->assertOrderInCompany($order);
        abort_if($order->status !== ProductionOrder::STATUS_DRAFT, 403);

        $this->pageTitle = __('production::app.saveDraft');
        $this->order = $order;
        $this->addProductData();

        return view('production::orders.edit', $this->data);
    }

    public function update(UpdateProductionOrderRequest $request, ProductionOrder $order): RedirectResponse
    {
        $this->assertEditProductionOrders();
        $this->assertOrderInCompany($order);
        abort_if($order->status !== ProductionOrder::STATUS_DRAFT, 403);

        $order->update($request->validated());

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
                ProductionBatch::query()->create([
                    'company_id' => $order->company_id,
                    'production_order_id' => $order->id,
                    'batch_code' => 'PB-'.$order->id.'-'.strtoupper(substr(str_replace('.', '', uniqid('', true)), -8)),
                ]);
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

    protected function addProductData(): void
    {
        $companyId = (int) company()->id;

        $this->finishedGoods = Product::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('type', 'goods')
            ->orderBy('name')
            ->get(['id', 'name']);

        $this->boms = ProductionBom::query()
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
