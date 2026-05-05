<?php

namespace Modules\Production\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Production\Entities\ProductionBatch;
use Modules\Production\Entities\ProductionBatchConsumption;
use Modules\Production\Entities\ProductionBatchOutput;
use Modules\Production\Http\Concerns\HandlesProductionErrors;
use Modules\Production\Http\Requests\StoreProductionBatchConsumptionRequest;
use Modules\Production\Http\Requests\StoreProductionBatchOutputRequest;
use Modules\Production\Services\ProductionPostingService;
use Modules\Production\Support\ProductionTenantAccess;
use Modules\Warehouse\Entities\WarehouseProductBatch;

class ProductionBatchController extends AccountBaseController
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

    public function show(ProductionBatch $batch): View
    {
        $this->assertViewProductionOrders();
        $this->assertBatchInCompany($batch);

        $batch->load([
            'order.outputProduct',
            'consumptions.componentProduct',
            'consumptions.warehouseProductBatch',
            'outputs',
        ]);

        $this->pageTitle = __('production::app.batchDetail').' '.$batch->batch_code;
        $this->batch = $batch;
        $companyId = (int) company()->id;

        $this->componentProducts = Product::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('type', 'goods')
            ->orderBy('name')
            ->get(['id', 'name']);

        $order = $batch->order;
        $this->rmBatches = WarehouseProductBatch::query()
            ->where('company_id', $companyId)
            ->where('warehouse_id', $order->rm_warehouse_id)
            ->where('quantity', '>', 0)
            ->orderByDesc('id')
            ->limit(300)
            ->get();

        return view('production::batches.show', $this->data);
    }

    public function trace(ProductionBatch $batch): View
    {
        $this->assertViewProductionOrders();
        $this->assertBatchInCompany($batch);

        $batch->load(['order', 'consumptions.warehouseProductBatch', 'outputs']);

        $outboundMovements = StockMovement::query()
            ->where('reference_type', ProductionBatch::class)
            ->where('reference_id', $batch->id)
            ->where('movement_type', 'outbound')
            ->orderBy('id')
            ->get();

        $inboundMovements = StockMovement::query()
            ->where('reference_type', ProductionBatch::class)
            ->where('reference_id', $batch->id)
            ->where('movement_type', 'inbound')
            ->orderBy('id')
            ->get();

        $this->pageTitle = __('production::app.traceability');
        $this->batch = $batch;
        $this->outboundMovements = $outboundMovements;
        $this->inboundMovements = $inboundMovements;

        return view('production::batches.trace', $this->data);
    }

    public function storeConsumption(StoreProductionBatchConsumptionRequest $request, ProductionBatch $batch): RedirectResponse
    {
        $this->assertEditProductionOrders();
        $this->assertBatchInCompany($batch);

        ProductionBatchConsumption::query()->create(array_merge($request->validated(), [
            'company_id' => company()->id,
            'production_batch_id' => $batch->id,
            'line_order' => (int) $batch->consumptions()->max('line_order') + 1,
        ]));

        return back()->with('success', __('messages.recordSaved'));
    }

    public function postConsumptions(Request $request, ProductionBatch $batch): RedirectResponse
    {
        $this->assertEditProductionOrders();
        $this->assertBatchInCompany($batch);

        try {
            $this->posting->postConsumptionsForBatch($batch->fresh());
        } catch (\Throwable $e) {
            return $this->handleProductionThrowable($request, 'production_post_consumptions', $e);
        }

        return back()->with('success', __('messages.updateSuccess'));
    }

    public function storeOutput(StoreProductionBatchOutputRequest $request, ProductionBatch $batch): RedirectResponse
    {
        $this->assertEditProductionOrders();
        $this->assertBatchInCompany($batch);

        $order = $batch->order;
        ProductionBatchOutput::query()->create(array_merge($request->validated(), [
            'company_id' => company()->id,
            'production_batch_id' => $batch->id,
            'output_product_id' => $order->output_product_id,
        ]));

        return back()->with('success', __('messages.recordSaved'));
    }

    public function postFgReceipt(Request $request, ProductionBatchOutput $output): RedirectResponse
    {
        $this->assertEditProductionOrders();
        $output->load('batch.order');
        $batch = $output->batch;
        if ($batch === null) {
            abort(404);
        }
        $this->assertBatchInCompany($batch);

        try {
            $this->posting->postFinishedGoodsReceipt($output->fresh());
        } catch (\Throwable $e) {
            return $this->handleProductionThrowable($request, 'production_post_fg_receipt', $e);
        }

        return redirect()
            ->route('production.orders.show', $batch->order)
            ->with('success', __('messages.updateSuccess'));
    }

    protected function assertViewProductionOrders(): void
    {
        $p = user()->permission('view_production_orders');
        abort_if(! in_array($p, ['all', 'added', 'owned', 'both'], true), 403);
    }

    protected function assertEditProductionOrders(): void
    {
        $p = user()->permission('edit_production_orders');
        abort_if(! in_array($p, ['all', 'added', 'owned', 'both'], true), 403);
    }

    protected function assertBatchInCompany(ProductionBatch $batch): void
    {
        $batch->loadMissing('order');
        $order = $batch->order;
        abort_if($order === null || (int) $order->company_id !== (int) company()->id, 403);
    }
}
