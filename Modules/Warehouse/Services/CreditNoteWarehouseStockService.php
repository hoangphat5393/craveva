<?php

namespace Modules\Warehouse\Services;

use App\Models\CreditNoteItem;
use App\Models\CreditNotes;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\StockMovement;
use App\Scopes\CompanyScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Entities\SalesDo;
use Modules\Warehouse\Contracts\SalesReturnInboundGateInterface;
use Modules\Warehouse\Entities\Warehouse;

/**
 * Posts inbound stock for sales returns (credit note lines) and reverses on credit note delete.
 *
 * Idempotency: stock_movements.idempotency_key per credit_note_id + line id (inbound and reversal-outbound).
 * Warehouse: credit_note_items.warehouse_id if set; else shipment DO that applied outbound for same order+product;
 * else same default resolution as sales invoice (client / company default warehouse).
 */
class CreditNoteWarehouseStockService
{
    public function __construct(
        protected StockMovementService $stockMovement,
        protected InvoiceWarehouseStockService $invoiceWarehouseStock,
        protected WarehouseFlowConfigService $flowConfig,
        protected SalesReturnInboundGateInterface $salesReturnInboundGate
    ) {}

    public function postInboundForCreditNoteItem(CreditNoteItem $item): void
    {
        if (function_exists('isSeedingData') && isSeedingData()) {
            return;
        }

        $item->loadMissing(['creditNote.invoice.clientdetails']);
        $creditNote = $item->creditNote;
        if (! $creditNote instanceof CreditNotes) {
            return;
        }

        $companyId = $this->resolveCompanyId($creditNote);
        if ($companyId === null || $companyId <= 0) {
            return;
        }

        if (! $this->invoiceWarehouseStock->isEnabled($companyId)) {
            return;
        }

        if ($item->type !== 'item' || ! $item->product_id) {
            return;
        }

        if (! $this->salesReturnInboundGate->allowInboundPosting($item)) {
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

        $invoice = $creditNote->invoice;
        if (! $invoice instanceof Invoice) {
            return;
        }

        $warehouseId = $this->resolveWarehouseIdForLine($item, $invoice, $companyId);
        if ($warehouseId <= 0) {
            return;
        }

        $inboundKey = $this->inboundIdempotencyKey((int) $creditNote->id, (int) $item->id);
        if ($this->movementExists($companyId, 'inbound', $inboundKey)) {
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
            'reference_type' => CreditNotes::class,
            'reference_id' => (int) $creditNote->id,
            'idempotency_key' => $inboundKey,
        ]);
    }

    public function reverseInboundForCreditNote(CreditNotes $creditNote): void
    {
        if (function_exists('isSeedingData') && isSeedingData()) {
            return;
        }

        $companyId = $this->resolveCompanyId($creditNote);
        if ($companyId === null || $companyId <= 0) {
            return;
        }

        if (! $this->invoiceWarehouseStock->isEnabled($companyId)) {
            return;
        }

        $creditNote->loadMissing('items');

        DB::transaction(function () use ($creditNote, $companyId) {
            foreach ($creditNote->items as $item) {
                $this->reverseInboundForCreditNoteItem($item, $creditNote, $companyId);
            }
        });
    }

    protected function reverseInboundForCreditNoteItem(CreditNoteItem $item, CreditNotes $creditNote, int $companyId): void
    {
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

        $invoice = $creditNote->invoice;
        if (! $invoice instanceof Invoice) {
            return;
        }

        $warehouseId = $this->resolveWarehouseIdForLine($item, $invoice, $companyId);
        if ($warehouseId <= 0) {
            return;
        }

        $inboundKey = $this->inboundIdempotencyKey((int) $creditNote->id, (int) $item->id);
        if (! $this->movementExists($companyId, 'inbound', $inboundKey)) {
            return;
        }

        $reversalKey = $this->reversalOutboundIdempotencyKey((int) $creditNote->id, (int) $item->id);
        if ($this->movementExists($companyId, 'outbound', $reversalKey)) {
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
            'reference_type' => 'credit_note_stock_reversal',
            'reference_id' => (int) $creditNote->id,
            'idempotency_key' => $reversalKey,
        ]);
    }

    protected function resolveWarehouseIdForLine(CreditNoteItem $item, Invoice $invoice, int $companyId): int
    {
        if (Schema::hasColumn('credit_note_items', 'warehouse_id') && $item->warehouse_id) {
            $wid = (int) $item->warehouse_id;
            if (Warehouse::where('id', $wid)->where('company_id', $companyId)->exists()) {
                return $wid;
            }
        }

        if (
            $this->flowConfig->salesOutboundMode($companyId) === 'shipment'
            && $invoice->order_id
            && class_exists(SalesDo::class)
        ) {
            $do = SalesDo::query()
                ->where('company_id', $companyId)
                ->where('order_id', (int) $invoice->order_id)
                ->where('outbound_stock_applied', true)
                ->whereHas('items', function ($q) use ($item) {
                    $q->where('product_id', (int) $item->product_id);
                })
                ->orderByDesc('id')
                ->first();

            if ($do && $do->warehouse_id) {
                return (int) $do->warehouse_id;
            }
        }

        return $this->invoiceWarehouseStock->resolveDefaultWarehouseIdForInvoice($invoice);
    }

    protected function resolveCompanyId(CreditNotes $creditNote): ?int
    {
        if ($creditNote->company_id) {
            return (int) $creditNote->company_id;
        }

        $creditNote->loadMissing('invoice');

        return $creditNote->invoice && $creditNote->invoice->company_id
            ? (int) $creditNote->invoice->company_id
            : null;
    }

    protected function inboundIdempotencyKey(int $creditNoteId, int $creditNoteItemId): string
    {
        return 'credit-note-inbound:'.$creditNoteId.':'.$creditNoteItemId;
    }

    protected function reversalOutboundIdempotencyKey(int $creditNoteId, int $creditNoteItemId): string
    {
        return 'credit-note-reversal-outbound:'.$creditNoteId.':'.$creditNoteItemId;
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
