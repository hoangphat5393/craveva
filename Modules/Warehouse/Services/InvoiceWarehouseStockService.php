<?php

namespace Modules\Warehouse\Services;

use App\Models\ClientDetails;
use App\Models\Invoice;
use App\Models\InvoiceItems;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Warehouse\Entities\InvoiceWarehouseStockPosting;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseProductStock;
use Modules\Warehouse\Exceptions\WarehouseBusinessException;

/**
 * Posts sales outbound from invoices to StockMovementService and reverses on delete/draft.
 *
 * Trigger (v1): invoice status is not "draft" and invoice is not a credit note — stock is synced on create/update.
 * Warehouse resolution: client default_warehouse_id → company default warehouse → exception if none.
 */
class InvoiceWarehouseStockService
{
    public function __construct(
        protected StockMovementService $stockMovement
    ) {}

    public function isEnabled(): bool
    {
        if (! (bool) config('warehouse.sales_outbound_enabled', false)) {
            return false;
        }

        if (! function_exists('module_enabled') || ! module_enabled('Warehouse')) {
            return false;
        }

        if (! function_exists('user_modules')) {
            return false;
        }

        return in_array('warehouse', user_modules() ?? [], true);
    }

    public function shouldPostOutboundFromInvoice(): bool
    {
        // Option B orchestration:
        // - mode=shipment => stock outbound happens when shipment is shipped, never here.
        // - mode=invoice  => keep legacy invoice outbound behavior.
        return config('warehouse.sales_outbound_mode', 'invoice') === 'invoice';
    }

    /**
     * Full sync: reverse previous postings then post current lines (idempotent net state).
     */
    public function syncInvoiceStock(Invoice $invoice): void
    {
        if (! $this->isEnabled() || ! $this->shouldPostOutboundFromInvoice()) {
            return;
        }

        if (function_exists('isSeedingData') && isSeedingData()) {
            return;
        }

        if (! $invoice->company_id) {
            return;
        }

        DB::transaction(function () use ($invoice) {
            $this->reverseAllPostings($invoice);

            if (! $this->shouldPostOutbound($invoice)) {
                return;
            }

            $invoice->loadMissing(['items', 'clientdetails']);

            foreach ($invoice->items as $item) {
                if (! $item->product_id || $item->type !== 'item') {
                    continue;
                }

                $product = Product::withoutGlobalScopes()->find($item->product_id);
                if (! $product || ($product->type ?? 'goods') === 'service') {
                    continue;
                }

                $warehouseId = $this->resolveWarehouseId($invoice);
                $qty = (float) $item->quantity;
                if ($qty <= 0) {
                    continue;
                }

                $payload = [
                    'company_id' => (int) $invoice->company_id,
                    'warehouse_id' => $warehouseId,
                    'product_id' => (int) $item->product_id,
                    'quantity' => $qty,
                    'batch_number' => null,
                    'expiry_date' => null,
                    'reference_type' => Invoice::class,
                    'reference_id' => $invoice->id,
                ];

                $this->stockMovement->recordOutbound($payload);

                InvoiceWarehouseStockPosting::create([
                    'company_id' => (int) $invoice->company_id,
                    'invoice_id' => $invoice->id,
                    'invoice_item_id' => $item->id,
                    'warehouse_id' => $warehouseId,
                    'product_id' => (int) $item->product_id,
                    'quantity' => $qty,
                ]);
            }
        });
    }

    public function reverseAllPostings(Invoice $invoice): void
    {
        if (! $this->isEnabled() || ! $this->shouldPostOutboundFromInvoice()) {
            return;
        }

        if (function_exists('isSeedingData') && isSeedingData()) {
            return;
        }

        $postings = InvoiceWarehouseStockPosting::where('invoice_id', $invoice->id)->get();
        if ($postings->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($postings, $invoice) {
            foreach ($postings as $posting) {
                $this->stockMovement->recordInbound([
                    'company_id' => (int) $posting->company_id,
                    'warehouse_id' => (int) $posting->warehouse_id,
                    'product_id' => (int) $posting->product_id,
                    'quantity' => (float) $posting->quantity,
                    'batch_number' => null,
                    'expiry_date' => null,
                    'reference_type' => 'invoice_stock_reversal',
                    'reference_id' => $invoice->id,
                ]);
                $posting->delete();
            }
        });
    }

    public function shouldPostOutbound(Invoice $invoice): bool
    {
        if ((int) ($invoice->credit_note ?? 0) !== 0) {
            return false;
        }

        return $invoice->status !== 'draft';
    }

    protected function resolveWarehouseId(Invoice $invoice): int
    {
        $companyId = (int) $invoice->company_id;

        $clientDetails = $invoice->clientdetails;
        if ($clientDetails && $clientDetails->default_warehouse_id) {
            $wid = (int) $clientDetails->default_warehouse_id;
            if (Warehouse::where('id', $wid)->where('company_id', $companyId)->exists()) {
                return $wid;
            }
        }

        $defaultWh = Warehouse::where('company_id', $companyId)->where('is_default', true)->first();
        if ($defaultWh) {
            return (int) $defaultWh->id;
        }

        $any = Warehouse::where('company_id', $companyId)->where('status', 'active')->orderBy('id')->first();
        if ($any) {
            return (int) $any->id;
        }

        throw new WarehouseBusinessException(__('warehouse::app.err_no_warehouse_for_invoice'), [
            'invoice_id' => $invoice->id,
            'company_id' => $companyId,
        ]);
    }

    /**
     * Validate invoice line quantities against warehouse stock (for direct stock check on store/update).
     *
     * @param  int|null  $excludeInvoiceId  When updating an invoice, exclude its lines from "committed unpaid" totals.
     * @return array<int> Product IDs that exceed available stock
     */
    public function validateRequestLinesAgainstWarehouse(Request $request, ?int $excludeInvoiceId = null): array
    {
        if (! $this->isEnabled() || $request->do_it_later !== 'direct') {
            return [];
        }

        $productIds = $request->product_id;
        $quantities = $request->quantity;
        if (! is_array($productIds) || ! is_array($quantities)) {
            return [];
        }

        $companyId = (int) (company()?->id ?? 0);
        if ($companyId <= 0) {
            return [];
        }

        $warehouseId = $this->resolveWarehouseIdFromRequestClient($request, $companyId);
        if ($warehouseId <= 0) {
            return array_values(array_filter(array_map('intval', (array) $request->product_id)));
        }

        $requiredByProduct = [];
        foreach ($productIds as $key => $productId) {
            if ($productId === null) {
                continue;
            }
            $product = Product::find($productId);
            if (! $product || $product->type === 'service') {
                continue;
            }
            $q = (float) ($quantities[$key] ?? 0);
            if ($q <= 0) {
                continue;
            }
            $requiredByProduct[$productId] = ($requiredByProduct[$productId] ?? 0) + $q;
        }

        $bad = [];
        foreach ($requiredByProduct as $productId => $need) {
            $available = (float) (WarehouseProductStock::query()
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->value('quantity') ?? 0);

            $committed = $this->committedUnpaidInvoiceQuantityForProduct($companyId, (int) $productId, $excludeInvoiceId);

            if (($available - $committed) < $need) {
                $bad[] = (int) $productId;
            }
        }

        return array_values(array_unique($bad));
    }

    /**
     * True when a default warehouse can be resolved for this request (client / company default / first active).
     */
    public function validateRequestHasResolvableWarehouse(Request $request): bool
    {
        if (! $this->isEnabled() || $request->do_it_later !== 'direct') {
            return true;
        }

        $companyId = (int) (company()?->id ?? 0);
        if ($companyId <= 0) {
            return false;
        }

        return $this->resolveWarehouseIdFromRequestClient($request, $companyId) > 0;
    }

    protected function resolveWarehouseIdFromRequestClient(Request $request, int $companyId): int
    {
        $clientId = $request->client_id ?? null;
        if ($clientId) {
            $details = ClientDetails::where('user_id', $clientId)->first();
            if ($details && $details->default_warehouse_id) {
                $wid = (int) $details->default_warehouse_id;
                if (Warehouse::where('id', $wid)->where('company_id', $companyId)->exists()) {
                    return $wid;
                }
            }
        }

        $defaultWh = Warehouse::where('company_id', $companyId)->where('is_default', true)->first();
        if ($defaultWh) {
            return (int) $defaultWh->id;
        }

        $any = Warehouse::where('company_id', $companyId)->where('status', 'active')->orderBy('id')->first();

        return $any ? (int) $any->id : 0;
    }

    /**
     * Other unpaid invoices' committed qty for same product (legacy-style guard).
     */
    protected function committedUnpaidInvoiceQuantityForProduct(int $companyId, int $productId, ?int $excludeInvoiceId = null): float
    {
        return (float) InvoiceItems::query()
            ->whereHas('invoice', function ($q) use ($companyId, $excludeInvoiceId) {
                $q->where('company_id', $companyId)->where('status', 'unpaid');
                if ($excludeInvoiceId) {
                    $q->where('id', '!=', $excludeInvoiceId);
                }
            })
            ->where('product_id', $productId)
            ->sum('quantity');
    }
}
