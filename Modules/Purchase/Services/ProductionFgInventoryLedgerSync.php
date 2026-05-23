<?php

declare(strict_types=1);

namespace Modules\Purchase\Services;

use Illuminate\Support\Facades\Schema;
use Modules\Production\Entities\ProductionBatchOutput;
use Modules\Purchase\Entities\PurchaseInventory;
use Modules\Purchase\Entities\PurchaseStockAdjustment;
use Modules\Warehouse\Entities\WarehouseProductBatch;
use Modules\Warehouse\Entities\WarehouseProductStock;

/**
 * Ensures Purchase Inventory ledger rows exist after Production FG inbound,
 * so Inventory list and Products stock_on_hand reflect warehouse truth.
 */
class ProductionFgInventoryLedgerSync
{
    public function canSync(): bool
    {
        return Schema::hasTable('purchase_stock_adjustments')
            && Schema::hasTable('purchase_inventory_adjustment')
            && Schema::hasTable('warehouses');
    }

    /**
     * Create or refresh purchase_stock_adjustments for product + warehouse after FG post.
     * Does not post warehouse movements (FG receipt already did).
     */
    public function ensureLedgerLineAfterFgReceipt(ProductionBatchOutput $output): void
    {
        if (! $this->canSync() || $output->posted_at === null) {
            return;
        }

        $companyId = (int) $output->company_id;
        $productId = (int) $output->output_product_id;
        $warehouseId = (int) $output->warehouse_id;

        if ($companyId <= 0 || $productId <= 0 || $warehouseId <= 0) {
            return;
        }

        $warehouseOnHand = $this->resolveWarehouseOnHand($warehouseId, $productId);

        $stockLine = PurchaseStockAdjustment::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if ($stockLine === null) {
            $stockLine = PurchaseStockAdjustment::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('product_id', $productId)
                ->whereNull('warehouse_id')
                ->first();
        }

        if ($stockLine === null) {
            $inventory = new PurchaseInventory;
            $inventory->company_id = $companyId;
            $inventory->date = now()->toDateString();
            $inventory->type = 'quantity';
            $inventory->warehouse_id = $warehouseId;
            $inventory->save();

            $stockLine = new PurchaseStockAdjustment;
            $stockLine->company_id = $companyId;
            $stockLine->inventory_id = $inventory->id;
            $stockLine->product_id = $productId;
            $stockLine->warehouse_id = $warehouseId;
            $stockLine->type = 'quantity';
            $stockLine->date = $inventory->date;
            $stockLine->status = 'converted';
            $stockLine->reference_number = $this->referenceNumberForOutput($output);
            $stockLine->description = __('purchase::messages.productionFgInventoryLedgerDescription', [
                'batch' => (string) $output->batch_number,
            ]);
        } else {
            if ($stockLine->inventory_id === null) {
                $inventory = new PurchaseInventory;
                $inventory->company_id = $companyId;
                $inventory->date = now()->toDateString();
                $inventory->type = 'quantity';
                $inventory->warehouse_id = $warehouseId;
                $inventory->save();
                $stockLine->inventory_id = $inventory->id;
            } else {
                $inventory = PurchaseInventory::withoutGlobalScopes()
                    ->where('id', $stockLine->inventory_id)
                    ->first();

                if ($inventory !== null && Schema::hasColumn('purchase_inventory_adjustment', 'warehouse_id')) {
                    $inventory->warehouse_id = $warehouseId;
                    $inventory->save();
                }
            }

            $stockLine->warehouse_id = $warehouseId;
        }

        $stockLine->net_quantity = $warehouseOnHand;
        $stockLine->batch_number = $output->batch_number !== null && $output->batch_number !== ''
            ? (string) $output->batch_number
            : $stockLine->batch_number;

        if ($output->expiration_date !== null) {
            $stockLine->expiration_date = $output->expiration_date;
        }

        if ($output->manufacturing_date !== null) {
            $stockLine->manufacturing_date = $output->manufacturing_date;
        }

        $stockLine->status = $stockLine->status ?: 'converted';
        $stockLine->save();
    }

    /**
     * @return list<array{output_id: int, product_id: int, warehouse_id: int, action: string, note: string}>
     */
    public function backfillPostedOutputsForCompany(int $companyId, bool $dryRun = false): array
    {
        if (! $this->canSync()) {
            return [];
        }

        $outputs = ProductionBatchOutput::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->whereNotNull('posted_at')
            ->orderBy('id')
            ->get();

        $results = [];

        foreach ($outputs as $output) {
            $exists = PurchaseStockAdjustment::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('product_id', (int) $output->output_product_id)
                ->where('warehouse_id', (int) $output->warehouse_id)
                ->exists();

            if ($dryRun) {
                $results[] = [
                    'output_id' => (int) $output->id,
                    'product_id' => (int) $output->output_product_id,
                    'warehouse_id' => (int) $output->warehouse_id,
                    'action' => $exists ? 'would_refresh' : 'would_create',
                    'note' => 'Ledger line for product #' . $output->output_product_id . ' @ warehouse #' . $output->warehouse_id,
                ];

                continue;
            }

            try {
                $this->ensureLedgerLineAfterFgReceipt($output);
                $results[] = [
                    'output_id' => (int) $output->id,
                    'product_id' => (int) $output->output_product_id,
                    'warehouse_id' => (int) $output->warehouse_id,
                    'action' => $exists ? 'refreshed' : 'created',
                    'note' => 'Synced ledger from warehouse on-hand',
                ];
            } catch (\Throwable $e) {
                $results[] = [
                    'output_id' => (int) $output->id,
                    'product_id' => (int) $output->output_product_id,
                    'warehouse_id' => (int) $output->warehouse_id,
                    'action' => 'error',
                    'note' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    protected function resolveWarehouseOnHand(int $warehouseId, int $productId): float
    {
        if (class_exists(WarehouseProductStock::class)) {
            $stockQty = WarehouseProductStock::query()
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->value('quantity');

            if ($stockQty !== null) {
                return (float) $stockQty;
            }
        }

        if (class_exists(WarehouseProductBatch::class)) {
            return (float) WarehouseProductBatch::query()
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->sum('quantity');
        }

        return 0.0;
    }

    protected function referenceNumberForOutput(ProductionBatchOutput $output): string
    {
        return 'PROD-OUT-' . $output->id;
    }
}
