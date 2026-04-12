<?php

namespace Modules\Warehouse\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Scopes\CompanyScope;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Entities\PurchaseBill;
use Modules\Purchase\Entities\PurchaseVendorCredit;
use Modules\Purchase\Entities\PurchaseVendorItem;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Exceptions\WarehouseBusinessException;

/**
 * Posts outbound stock for purchase returns (Vendor Credit lines) and reverses on delete / line change.
 *
 * Idempotency: outbound keys `vendor-credit-outbound:{credit}:{item}` then `:{n}` after each reversal cycle;
 * reversal inbounds `vendor-credit-reversal-inbound:{credit}:{item}` with optional `:{n}`.
 * Warehouse: purchase_vendor_items.warehouse_id if set; else PO warehouse from linked bill; else company default warehouse.
 */
class VendorCreditWarehouseStockService
{
    public function __construct(
        protected StockMovementService $stockMovement
    ) {}

    public function postOutboundForVendorCreditItem(PurchaseVendorItem $item): void
    {
        if (function_exists('isSeedingData') && isSeedingData()) {
            return;
        }

        $item->loadMissing(['vendorCredit.bills.order']);
        $credit = $item->vendorCredit;
        if (! $credit instanceof PurchaseVendorCredit) {
            return;
        }

        $companyId = $this->resolveCompanyId($credit);
        if ($companyId === null || $companyId <= 0) {
            return;
        }

        if (! $this->isLedgerIntegrationEnabled()) {
            return;
        }

        if ($item->type !== 'item' || ! $item->product_id) {
            return;
        }

        $product = Product::withoutGlobalScope(CompanyScope::class)->find($item->product_id);
        if (! $product || ($product->type ?? 'goods') === 'service') {
            return;
        }

        $qty = (float) $item->quantity;
        if ($qty <= 0) {
            return;
        }

        $warehouseId = $this->resolveWarehouseIdForLine($item, $credit, $companyId);
        if ($warehouseId <= 0) {
            return;
        }

        $creditId = (int) $credit->id;
        $itemId = (int) $item->id;
        $outboundKey = $this->resolveOutboundIdempotencyKeyForPost($companyId, $creditId, $itemId);
        if ($this->movementExists($companyId, 'outbound', $outboundKey)) {
            return;
        }

        $this->stockMovement->recordOutbound([
            'company_id' => $companyId,
            'warehouse_id' => $warehouseId,
            'product_id' => (int) $item->product_id,
            'quantity' => $qty,
            'unit_id' => $item->unit_id ? (int) $item->unit_id : null,
            'batch_number' => null,
            'expiry_date' => null,
            'reference_type' => PurchaseVendorCredit::class,
            'reference_id' => $creditId,
            'idempotency_key' => $outboundKey,
        ]);
    }

    public function resyncOutboundForVendorCreditItem(PurchaseVendorItem $item): void
    {
        $this->reverseOutboundForVendorCreditItem($this->snapshotItemStateBeforeChange($item));
        $this->postOutboundForVendorCreditItem($item->fresh());
    }

    /**
     * Build a read-only view of the line as it was when outbound was posted (for reversal after edits).
     */
    protected function snapshotItemStateBeforeChange(PurchaseVendorItem $item): PurchaseVendorItem
    {
        $snapshot = new PurchaseVendorItem;
        $snapshot->id = $item->id;
        $snapshot->credit_id = $item->credit_id;
        $snapshot->type = $item->wasChanged('type')
            ? $item->getOriginal('type')
            : $item->type;
        $snapshot->quantity = $item->wasChanged('quantity')
            ? (float) $item->getOriginal('quantity')
            : (float) $item->quantity;
        $snapshot->product_id = $item->wasChanged('product_id')
            ? $item->getOriginal('product_id')
            : $item->product_id;
        $snapshot->unit_id = $item->wasChanged('unit_id')
            ? $item->getOriginal('unit_id')
            : $item->unit_id;
        if (Schema::hasColumn('purchase_vendor_items', 'warehouse_id')) {
            $snapshot->warehouse_id = $item->wasChanged('warehouse_id')
                ? $item->getOriginal('warehouse_id')
                : $item->warehouse_id;
        }
        $credit = $item->relationLoaded('vendorCredit') ? $item->vendorCredit : $item->vendorCredit()->first();
        $snapshot->setRelation('vendorCredit', $credit);

        return $snapshot;
    }

    public function reverseOutboundForVendorCreditItem(PurchaseVendorItem $item): void
    {
        if (function_exists('isSeedingData') && isSeedingData()) {
            return;
        }

        $item->loadMissing(['vendorCredit.bills.order']);
        $credit = $item->vendorCredit;
        if (! $credit instanceof PurchaseVendorCredit) {
            return;
        }

        $companyId = $this->resolveCompanyId($credit);
        if ($companyId === null || $companyId <= 0) {
            return;
        }

        if (! $this->isLedgerIntegrationEnabled()) {
            return;
        }

        $creditId = (int) $credit->id;
        $itemId = (int) $item->id;
        $nOut = $this->countOutboundKeysForLine($companyId, $creditId, $itemId);
        $nRev = $this->countReversalKeysForLine($companyId, $creditId, $itemId);
        if ($nOut <= $nRev) {
            return;
        }

        $reversalKey = $this->nextReversalInboundIdempotencyKey($companyId, $creditId, $itemId);
        if ($this->movementExists($companyId, 'inbound', $reversalKey)) {
            return;
        }

        $warehouseId = $this->resolveWarehouseIdForLine($item, $credit, $companyId);
        if ($warehouseId <= 0 || ! $item->product_id) {
            return;
        }

        $qty = (float) $item->quantity;
        if ($qty <= 0) {
            return;
        }

        $this->stockMovement->recordInbound([
            'company_id' => $companyId,
            'warehouse_id' => $warehouseId,
            'product_id' => (int) $item->product_id,
            'quantity' => $qty,
            'unit_id' => $item->unit_id ? (int) $item->unit_id : null,
            'batch_number' => null,
            'expiry_date' => null,
            'reference_type' => 'purchase_vendor_credit_stock_reversal',
            'reference_id' => $creditId,
            'idempotency_key' => $reversalKey,
        ]);
    }

    public function reverseAllOutboundForVendorCredit(PurchaseVendorCredit $credit): void
    {
        if (function_exists('isSeedingData') && isSeedingData()) {
            return;
        }

        $companyId = $this->resolveCompanyId($credit);
        if ($companyId === null || $companyId <= 0) {
            return;
        }

        if (! $this->isLedgerIntegrationEnabled()) {
            return;
        }

        $credit->loadMissing('items');

        foreach ($credit->items as $item) {
            $this->reverseOutboundForVendorCreditItem($item);
        }
    }

    protected function resolveWarehouseIdForLine(PurchaseVendorItem $item, PurchaseVendorCredit $credit, int $companyId): int
    {
        if (Schema::hasColumn('purchase_vendor_items', 'warehouse_id') && $item->warehouse_id) {
            $wid = (int) $item->warehouse_id;
            if (Warehouse::where('id', $wid)->where('company_id', $companyId)->exists()) {
                return $wid;
            }
        }

        $credit->loadMissing('bills.order');
        $bill = $credit->bills;
        if ($bill instanceof PurchaseBill && $bill->order && $bill->order->warehouse_id) {
            $wid = (int) $bill->order->warehouse_id;
            if (Warehouse::where('id', $wid)->where('company_id', $companyId)->exists()) {
                return $wid;
            }
        }

        return $this->resolveDefaultWarehouseIdForCompany($companyId);
    }

    protected function resolveDefaultWarehouseIdForCompany(int $companyId): int
    {
        $defaultWh = Warehouse::where('company_id', $companyId)->where('is_default', true)->first();
        if ($defaultWh) {
            return (int) $defaultWh->id;
        }

        $any = Warehouse::where('company_id', $companyId)->where('status', 'active')->orderBy('id')->first();
        if ($any) {
            return (int) $any->id;
        }

        throw new WarehouseBusinessException(__('warehouse::app.err_no_warehouse_for_invoice'), [
            'company_id' => $companyId,
        ]);
    }

    protected function resolveCompanyId(PurchaseVendorCredit $credit): ?int
    {
        if ($credit->company_id) {
            return (int) $credit->company_id;
        }

        return null;
    }

    protected function isLedgerIntegrationEnabled(): bool
    {
        if (! function_exists('module_enabled') || ! module_enabled('Warehouse')) {
            return false;
        }

        if (function_exists('user') && ! user()) {
            return true;
        }

        if (! function_exists('user_modules')) {
            return true;
        }

        return in_array('warehouse', user_modules() ?? [], true);
    }

    protected function outboundBaseKey(int $creditId, int $itemId): string
    {
        return 'vendor-credit-outbound:'.$creditId.':'.$itemId;
    }

    protected function countOutboundKeysForLine(int $companyId, int $creditId, int $itemId): int
    {
        $base = $this->outboundBaseKey($creditId, $itemId);

        return (int) StockMovement::query()
            ->where('company_id', $companyId)
            ->where('movement_type', 'outbound')
            ->where(function ($q) use ($base) {
                $q->where('idempotency_key', $base)
                    ->orWhere('idempotency_key', 'like', $base.':%');
            })
            ->count();
    }

    protected function countReversalKeysForLine(int $companyId, int $creditId, int $itemId): int
    {
        $base = 'vendor-credit-reversal-inbound:'.$creditId.':'.$itemId;

        return (int) StockMovement::query()
            ->where('company_id', $companyId)
            ->where('movement_type', 'inbound')
            ->where('reference_type', 'purchase_vendor_credit_stock_reversal')
            ->where('reference_id', $creditId)
            ->where(function ($q) use ($base) {
                $q->where('idempotency_key', $base)
                    ->orWhere('idempotency_key', 'like', $base.':%');
            })
            ->count();
    }

    /**
     * Next outbound idempotency key: first wave uses base key only; after a reversal (nOut === nRev), post uses base:{nOut}.
     * When nOut > nRev, return the latest posted key so duplicate posts no-op.
     */
    protected function resolveOutboundIdempotencyKeyForPost(int $companyId, int $creditId, int $itemId): string
    {
        $base = $this->outboundBaseKey($creditId, $itemId);
        $nRev = $this->countReversalKeysForLine($companyId, $creditId, $itemId);
        $nOut = $this->countOutboundKeysForLine($companyId, $creditId, $itemId);

        if ($nOut === 0) {
            return $base;
        }

        if ($nRev === 0) {
            return $base;
        }

        if ($nOut > $nRev) {
            return $nOut === 1 ? $base : $base.':'.($nOut - 1);
        }

        return $base.':'.$nOut;
    }

    protected function nextReversalInboundIdempotencyKey(int $companyId, int $creditId, int $itemId): string
    {
        $base = 'vendor-credit-reversal-inbound:'.$creditId.':'.$itemId;
        $n = (int) StockMovement::query()
            ->where('company_id', $companyId)
            ->where('movement_type', 'inbound')
            ->where('reference_type', 'purchase_vendor_credit_stock_reversal')
            ->where('reference_id', $creditId)
            ->where(function ($q) use ($base) {
                $q->where('idempotency_key', $base)
                    ->orWhere('idempotency_key', 'like', $base.':%');
            })
            ->count();

        return $n === 0 ? $base : $base.':'.$n;
    }

    protected function movementExists(int $companyId, string $movementType, string $key): bool
    {
        if (! Schema::hasColumn('stock_movements', 'idempotency_key')) {
            return false;
        }

        return StockMovement::query()
            ->where('company_id', $companyId)
            ->where('movement_type', $movementType)
            ->where('idempotency_key', $key)
            ->exists();
    }
}
