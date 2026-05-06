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
use Modules\Production\Entities\ProductionOrder;
use Modules\Production\Http\Concerns\HandlesProductionErrors;
use Modules\Production\Http\Requests\AssignWarehouseBatchToConsumptionRequest;
use Modules\Production\Http\Requests\StoreProductionBatchConsumptionRequest;
use Modules\Production\Http\Requests\StoreProductionBatchOutputRequest;
use Modules\Production\Services\ProductionFgQuantityPolicyService;
use Modules\Production\Services\ProductionPlannedConsumptionFromSnapshotService;
use Modules\Production\Services\ProductionPostingService;
use Modules\Production\Support\ProductionTenantAccess;
use Modules\Warehouse\Entities\WarehouseProductBatch;

class ProductionBatchController extends AccountBaseController
{
    use HandlesProductionErrors;

    public function __construct(
        protected ProductionPostingService $posting,
        protected ProductionPlannedConsumptionFromSnapshotService $plannedFromSnapshot,
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
            'order.bomSnapshotItems.componentProduct',
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

        $this->canApplyBomSnapshotPlanned = $order->bom_snapshot_at !== null
            && $order->batches()->count() === 1
            && $batch->posted_consumptions_at === null
            && $batch->consumptions->isEmpty()
            && in_array($order->status, [ProductionOrder::STATUS_RELEASED, ProductionOrder::STATUS_IN_PROGRESS], true);

        return view('production::batches.show', $this->data);
    }

    public function applyPlannedFromBomSnapshot(Request $request, ProductionBatch $batch): RedirectResponse
    {
        $this->assertEditProductionOrders();
        $this->assertBatchInCompany($batch);

        try {
            $this->plannedFromSnapshot->applySnapshotToBatch($batch->fresh(['order']));
        } catch (\Throwable $e) {
            return $this->handleProductionThrowable($request, 'production_apply_bom_snapshot_planned', $e);
        }

        return back()->with('success', __('messages.updateSuccess'));
    }

    public function assignConsumptionWarehouseBatch(AssignWarehouseBatchToConsumptionRequest $request, ProductionBatch $batch, ProductionBatchConsumption $consumption): RedirectResponse
    {
        $this->assertEditProductionOrders();
        $this->assertBatchInCompany($batch);

        abort_if((int) $consumption->production_batch_id !== (int) $batch->id, 404);
        abort_if($batch->posted_consumptions_at !== null, 403);

        $consumption->warehouse_product_batch_id = (int) $request->validated()['warehouse_product_batch_id'];
        $consumption->save();

        return back()->with('success', __('messages.updateSuccess'));
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

        $validated = $request->validated();
        $planned = (float) $order->planned_quantity;
        $incoming = (float) $validated['quantity'];

        /** @var ProductionFgQuantityPolicyService $fgPolicy */
        $fgPolicy = app(ProductionFgQuantityPolicyService::class);
        $existingTotal = $fgPolicy->registeredFgTotalForOrder($order);
        $projected = $existingTotal + $incoming;
        $snapshot = $fgPolicy->varianceSnapshot($planned, $projected);

        $output = ProductionBatchOutput::query()->create([
            'company_id' => company()->id,
            'production_batch_id' => $batch->id,
            'output_product_id' => $order->output_product_id,
            'quantity' => $incoming,
            'batch_number' => (string) $validated['batch_number'],
            'expiration_date' => $validated['expiration_date'] ?? null,
            'manufacturing_date' => $validated['manufacturing_date'] ?? null,
            'warehouse_id' => (int) $validated['warehouse_id'],
            'variance_reason' => $validated['variance_reason'] ?? null,
            ...$snapshot,
        ]);

        if ($batch->posted_consumptions_at !== null) {
            try {
                $this->posting->postFinishedGoodsReceipt($output->fresh());
            } catch (\Throwable $e) {
                return $this->handleProductionThrowable($request, 'production_auto_post_fg_receipt_after_output_save', $e);
            }

            return back()->with('success', __('messages.updateSuccess'));
        }

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
