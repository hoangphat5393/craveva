<?php

declare(strict_types=1);

namespace Modules\Purchase\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Entities\PurchaseInventory;
use Modules\Purchase\Entities\PurchaseProduct;
use Modules\Purchase\Entities\PurchaseStockAdjustment;
use Modules\Warehouse\Entities\WarehouseProductStock;
use Modules\Warehouse\Services\EnsureDefaultWarehouseService;
use Modules\Warehouse\Services\StockMovementService;
use Modules\Warehouse\Services\WarehouseUnitConversionService;

class ProductOpeningStockWarehouseSync
{
    public function __construct(
        protected EnsureDefaultWarehouseService $ensureDefaultWarehouse,
        protected WarehouseUnitConversionService $unitConversionService,
    ) {}

    public function canSync(): bool
    {
        return Schema::hasTable('warehouses')
            && class_exists(StockMovementService::class);
    }

    /**
     * Sync opening stock to default warehouse and attach warehouse_id on legacy lines.
     *
     * @throws \RuntimeException when track inventory with opening qty but no default warehouse
     */
    public function syncFromProductSave(
        PurchaseProduct $product,
        PurchaseStockAdjustment $stockLine,
        PurchaseInventory $inventoryHeader,
        float $openingQuantity
    ): void {
        if (! $this->canSync() || $openingQuantity <= 0.0000001) {
            return;
        }

        $companyId = (int) $product->company_id;
        $warehouseId = $this->ensureDefaultWarehouse->resolveDefaultWarehouseId($companyId);

        if ($warehouseId === null) {
            throw new \RuntimeException(
                __('purchase::messages.openingStockNoDefaultWarehouse', [
                    'command' => 'php artisan warehouse:ensure-default-for-companies',
                ])
            );
        }

        $productId = (int) $product->id;
        $targetBaseQty = $this->unitConversionService->convertToBase(
            $companyId,
            $productId,
            $openingQuantity,
            $product->unit_id !== null ? (int) $product->unit_id : null,
        );

        $stockLine->warehouse_id = $warehouseId;
        $stockLine->save();

        if (Schema::hasColumn('purchase_inventory_adjustment', 'warehouse_id')) {
            $inventoryHeader->warehouse_id = $warehouseId;
            $inventoryHeader->save();
        }

        $this->syncWarehouseStockAbsolute($companyId, $warehouseId, $productId, $targetBaseQty, $inventoryHeader);
    }

    protected function syncWarehouseStockAbsolute(
        int $companyId,
        int $warehouseId,
        int $productId,
        float $targetQuantity,
        PurchaseInventory $inventory
    ): void {
        $currentQuantity = 0.0;

        if (class_exists(WarehouseProductStock::class)) {
            $currentQuantity = (float) (WarehouseProductStock::query()
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->value('quantity') ?? 0);
        }

        $delta = round($targetQuantity - $currentQuantity, 6);

        if (abs($delta) < 0.000001) {
            return;
        }

        $movementService = app(StockMovementService::class);

        $basePayload = [
            'company_id' => $companyId,
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
            'batch_number' => null,
            'expiry_date' => null,
            'manufacturing_date' => null,
            'reference_type' => PurchaseInventory::class,
            'reference_id' => $inventory->id,
        ];

        if ($delta > 0) {
            $movementService->recordInbound($basePayload + ['quantity' => $delta]);
        } else {
            $movementService->recordOutbound($basePayload + ['quantity' => abs($delta)]);
        }
    }

    /**
     * @return list<array{adjustment_id: int, product_id: int, action: string, note: string}>
     */
    public function backfillLegacyLinesForCompany(int $companyId, bool $dryRun = false): array
    {
        if (! $this->canSync()) {
            return [];
        }

        $warehouseId = $this->ensureDefaultWarehouse->resolveDefaultWarehouseId($companyId);

        if ($warehouseId === null) {
            return [[
                'adjustment_id' => 0,
                'product_id' => 0,
                'action' => 'skipped',
                'note' => 'No default warehouse — run warehouse:ensure-default-for-companies',
            ]];
        }

        $lines = PurchaseStockAdjustment::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->whereNull('warehouse_id')
            ->where('net_quantity', '>', 0)
            ->whereHas('product', static function ($query): void {
                $query->withoutGlobalScopes()
                    ->where('track_inventory', '1');
            })
            ->with(['product'])
            ->orderBy('id')
            ->get();

        $results = [];

        foreach ($lines as $line) {
            $product = $line->product;

            if (! $product instanceof PurchaseProduct && ! $product instanceof Product) {
                $results[] = [
                    'adjustment_id' => (int) $line->id,
                    'product_id' => (int) $line->product_id,
                    'action' => 'skipped',
                    'note' => 'Product not found',
                ];

                continue;
            }

            $openingQty = (float) $line->net_quantity;

            if ($dryRun) {
                $results[] = [
                    'adjustment_id' => (int) $line->id,
                    'product_id' => (int) $line->product_id,
                    'action' => 'would_sync',
                    'note' => "Would sync {$openingQty} to warehouse #{$warehouseId}",
                ];

                continue;
            }

            $inventory = PurchaseInventory::withoutGlobalScopes()
                ->where('id', $line->inventory_id)
                ->first();

            if ($inventory === null) {
                $inventory = new PurchaseInventory;
                $inventory->company_id = $companyId;
                $inventory->date = $line->date ?? now()->toDateString();
                $inventory->type = 'quantity';
                $inventory->save();
                $line->inventory_id = $inventory->id;
            }

            try {
                $this->syncFromProductSave($product, $line, $inventory, $openingQty);
                $results[] = [
                    'adjustment_id' => (int) $line->id,
                    'product_id' => (int) $line->product_id,
                    'action' => 'synced',
                    'note' => "Synced {$openingQty} to warehouse #{$warehouseId}",
                ];
            } catch (\Throwable $e) {
                $results[] = [
                    'adjustment_id' => (int) $line->id,
                    'product_id' => (int) $line->product_id,
                    'action' => 'error',
                    'note' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
