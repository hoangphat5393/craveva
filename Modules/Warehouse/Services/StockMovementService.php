<?php

namespace Modules\Warehouse\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Scopes\CompanyScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Warehouse\Concerns\ScopesWarehouseProductBatchQuery;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseProductBatch;
use Modules\Warehouse\Entities\WarehouseProductStock;
use Modules\Warehouse\Exceptions\WarehouseBusinessException;

class StockMovementService
{
    use ScopesWarehouseProductBatchQuery;

    public function __construct(
        protected WarehouseFlowPolicyService $flowPolicy,
        protected WarehouseUnitConversionService $unitConversionService,
        protected WarehouseFlowConfigService $flowConfig
    ) {}

    public function recordInbound(array $payload): void
    {
        DB::transaction(function () use ($payload) {
            $this->applyInboundOnce($payload);
        });
    }

    /**
     * Multiple inbound lines in a single DB transaction (e.g. Delivery Order receiving).
     *
     * @param  array<int, array<string, mixed>>  $payloads
     */
    public function recordInboundBatch(array $payloads): void
    {
        if ($payloads === []) {
            return;
        }

        DB::transaction(function () use ($payloads) {
            foreach ($payloads as $payload) {
                $this->applyInboundOnce($payload);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function applyInboundOnce(array $payload): void
    {
        $rawQty = (float) ($payload['quantity'] ?? 0);
        $qty = $this->convertToBaseQuantity($payload, $rawQty);
        $payload['quantity'] = $qty;
        $payload['unit_id'] = $this->unitConversionService->baseUnitId((int) $payload['product_id']) ?: ($payload['unit_id'] ?? null);
        if ($qty <= 0) {
            throw new WarehouseBusinessException(__('warehouse::app.err_quantity_must_be_positive'), [
                'quantity' => $qty,
            ]);
        }

        $companyId = $this->requireCompanyId($payload);
        $this->assertWarehouseBelongsToCompany((int) $payload['warehouse_id'], $companyId);
        $this->assertProductBelongsToCompany((int) $payload['product_id'], $companyId);

        $batch = $this->lockOrCreateBatchRow($payload);
        $batch->quantity = (float) $batch->quantity + $qty;
        $batch->save();

        $this->syncLegacyWarehouseStock($payload['warehouse_id'], $payload['product_id']);
        $this->createMovement($payload, 'inbound');
    }

    public function recordOutbound(array $payload, ?bool $allowNegativeStock = null): void
    {
        DB::transaction(function () use ($payload, $allowNegativeStock) {
            $this->executeOutboundMovement($payload, $allowNegativeStock);
        });
    }

    /**
     * Outbound body (no transaction wrapper). Used by recordOutbound and recordTransfer.
     */
    protected function executeOutboundMovement(array $payload, ?bool $allowNegativeStock = null): void
    {
        $rawRequested = (float) ($payload['quantity'] ?? 0);
        $requested = $this->convertToBaseQuantity($payload, $rawRequested);
        $payload['quantity'] = $requested;
        $payload['unit_id'] = $this->unitConversionService->baseUnitId((int) $payload['product_id']) ?: ($payload['unit_id'] ?? null);
        if ($requested <= 0) {
            throw new WarehouseBusinessException(__('warehouse::app.err_quantity_must_be_positive'), [
                'quantity' => $requested,
            ]);
        }

        $companyId = $this->requireCompanyId($payload);
        $this->assertWarehouseBelongsToCompany((int) $payload['warehouse_id'], $companyId);
        $this->assertProductBelongsToCompany((int) $payload['product_id'], $companyId);
        $referenceType = (string) ($payload['reference_type'] ?? '');
        if (! in_array($referenceType, ['transfer', 'warehouse_transfer'], true)) {
            $this->flowPolicy->assertSellableOutboundWarehouse((int) $payload['warehouse_id'], $referenceType);
        }

        $rows = $this->resolveOutboundRows($payload);
        $available = (float) $rows->sum('quantity');
        $this->guardStockNotNegative($available, $requested, $allowNegativeStock, $companyId);

        $remaining = $requested;
        foreach ($rows as $row) {
            if ($remaining <= 0) {
                break;
            }

            $take = min($remaining, (float) $row->quantity);
            if ($take <= 0) {
                continue;
            }

            $row->quantity = (float) $row->quantity - $take;
            $row->save();
            $remaining -= $take;

            $linePayload = $payload;
            $linePayload['quantity'] = $take;
            $linePayload['batch_number'] = $row->batch_number;
            $linePayload['expiry_date'] = $row->expiration_date;
            $linePayload['fefo_fifo_rule'] = 'FEFO';
            $this->createMovement($linePayload, 'outbound');
        }

        $this->syncLegacyWarehouseStock($payload['warehouse_id'], $payload['product_id']);
    }

    public function recordTransfer(array $payload, ?bool $allowNegativeStock = null): void
    {
        if (empty($payload['warehouse_from_id']) || empty($payload['warehouse_to_id'])) {
            throw new WarehouseBusinessException(__('warehouse::app.err_transfer_missing_warehouses'), [
                'payload_keys' => array_keys($payload),
            ]);
        }

        if ((int) $payload['warehouse_from_id'] === (int) $payload['warehouse_to_id']) {
            throw new WarehouseBusinessException(__('warehouse::app.err_transfer_same_warehouse'));
        }

        $companyId = $this->requireCompanyId($payload);
        $this->assertWarehouseBelongsToCompany((int) $payload['warehouse_from_id'], $companyId);
        $this->assertWarehouseBelongsToCompany((int) $payload['warehouse_to_id'], $companyId);
        $this->assertProductBelongsToCompany((int) $payload['product_id'], $companyId);

        DB::transaction(function () use ($payload, $allowNegativeStock) {
            $outboundPayload = [
                'company_id' => $payload['company_id'] ?? null,
                'warehouse_id' => $payload['warehouse_from_id'],
                'product_id' => $payload['product_id'],
                'quantity' => $payload['quantity'],
                'batch_number' => $payload['batch_number'] ?? null,
                'expiry_date' => $payload['expiry_date'] ?? null,
                'reference_type' => $payload['reference_type'] ?? 'transfer',
                'reference_id' => $payload['reference_id'] ?? null,
            ];
            $this->executeOutboundMovement($outboundPayload, $allowNegativeStock);

            $inboundPayload = [
                'company_id' => $payload['company_id'] ?? null,
                'warehouse_id' => $payload['warehouse_to_id'],
                'product_id' => $payload['product_id'],
                'quantity' => $payload['quantity'],
                'batch_number' => $payload['batch_number'] ?? null,
                'expiry_date' => $payload['expiry_date'] ?? null,
                'manufacturing_date' => $payload['manufacturing_date'] ?? null,
                'reference_type' => $payload['reference_type'] ?? 'transfer',
                'reference_id' => $payload['reference_id'] ?? null,
            ];
            $this->applyInboundOnce($inboundPayload);
            // Single DB transaction (avoids nested savepoints from recordOutbound + recordInbound).
            // Ledger: outbound at source + inbound at destination only — no third movement row.
        });
    }

    public function isNegativeStockAllowed(?bool $override = null, ?int $companyId = null): bool
    {
        if (! is_null($override)) {
            return $override;
        }

        return $this->flowConfig->allowNegativeStock($companyId);
    }

    public function guardStockNotNegative(float $available, float $requested, ?bool $allowNegativeStock = null, ?int $companyId = null): void
    {
        if ($this->isNegativeStockAllowed($allowNegativeStock, $companyId)) {
            return;
        }

        if ($requested > $available) {
            throw new WarehouseBusinessException(
                __('warehouse::app.err_insufficient_stock', [
                    'available' => $this->formatQuantityForMessage($available),
                    'requested' => $this->formatQuantityForMessage($requested),
                ]),
                [
                    'available' => $available,
                    'requested' => $requested,
                ]
            );
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function requireCompanyId(array $payload): int
    {
        $companyId = isset($payload['company_id']) ? (int) $payload['company_id'] : 0;

        if ($companyId <= 0) {
            throw new WarehouseBusinessException(__('warehouse::app.err_company_context_missing'));
        }

        return $companyId;
    }

    protected function assertWarehouseBelongsToCompany(int $warehouseId, int $companyId): void
    {
        $exists = Warehouse::query()
            ->where('id', $warehouseId)
            ->where('company_id', $companyId)
            ->exists();

        if (! $exists) {
            throw new WarehouseBusinessException(__('warehouse::app.err_warehouse_not_in_company'), [
                'warehouse_id' => $warehouseId,
                'company_id' => $companyId,
            ]);
        }
    }

    protected function assertProductBelongsToCompany(int $productId, int $companyId): void
    {
        $exists = Product::withoutGlobalScope(CompanyScope::class)
            ->where('id', $productId)
            ->where('company_id', $companyId)
            ->exists();

        if (! $exists) {
            throw new WarehouseBusinessException(__('warehouse::app.err_product_not_in_company'), [
                'product_id' => $productId,
                'company_id' => $companyId,
            ]);
        }
    }

    protected function formatQuantityForMessage(float $value): string
    {
        $formatted = number_format($value, 4, '.', '');
        $rtrim = rtrim(rtrim($formatted, '0'), '.');

        return $rtrim === '' ? '0' : $rtrim;
    }

    public function sortForFefo(Collection $rows): Collection
    {
        return $rows->sort(function ($a, $b) {
            $aExpiry = $a->expiration_date;
            $bExpiry = $b->expiration_date;

            if (is_null($aExpiry) && is_null($bExpiry)) {
                return $a->id <=> $b->id;
            }
            if (is_null($aExpiry)) {
                return 1;
            }
            if (is_null($bExpiry)) {
                return -1;
            }

            return strcmp($aExpiry, $bExpiry);
        })->values();
    }

    protected function resolveOutboundRows(array $payload): Collection
    {
        $query = WarehouseProductBatch::query()
            ->lockForUpdate()
            ->where('warehouse_id', $payload['warehouse_id'])
            ->where('product_id', $payload['product_id'])
            ->where('quantity', '>', 0);

        if (! empty($payload['batch_number'])) {
            $query->where('batch_number', $payload['batch_number']);
        }

        if (! empty($payload['expiry_date'])) {
            $query->whereDate('expiration_date', $payload['expiry_date']);
        }

        return $this->sortForFefo($query->get());
    }

    protected function lockOrCreateBatchRow(array $payload): WarehouseProductBatch
    {
        $query = WarehouseProductBatch::query()
            ->lockForUpdate()
            ->where('warehouse_id', $payload['warehouse_id'])
            ->where('product_id', $payload['product_id']);
        $this->applyBatchIdentityToQuery($query, $payload);

        $row = $query->first();
        if ($row) {
            return $row;
        }

        return WarehouseProductBatch::create([
            'company_id' => $payload['company_id'] ?? null,
            'warehouse_id' => $payload['warehouse_id'],
            'product_id' => $payload['product_id'],
            'batch_number' => $payload['batch_number'] ?? null,
            'expiration_date' => $payload['expiry_date'] ?? null,
            'manufacturing_date' => $payload['manufacturing_date'] ?? null,
            'quantity' => 0,
            'reserved_quantity' => 0,
        ]);
    }

    protected function syncLegacyWarehouseStock(int $warehouseId, int $productId): void
    {
        $total = (float) WarehouseProductBatch::query()
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->sum('quantity');

        $legacy = WarehouseProductStock::firstOrCreate(
            ['warehouse_id' => $warehouseId, 'product_id' => $productId],
            ['quantity' => 0]
        );

        $legacy->quantity = $total;
        $legacy->save();
    }

    protected function createMovement(array $payload, string $type): void
    {
        $idempotencyKey = $payload['idempotency_key'] ?? null;
        $hasIdempotencyColumn = Schema::hasColumn('stock_movements', 'idempotency_key');
        if ($idempotencyKey && $hasIdempotencyColumn) {
            $alreadyExists = StockMovement::query()
                ->where('company_id', $payload['company_id'] ?? null)
                ->where('movement_type', $type)
                ->where('idempotency_key', $idempotencyKey)
                ->exists();

            if ($alreadyExists) {
                return;
            }
        }

        $movementType = match ($type) {
            'inbound' => 'inbound',
            'outbound' => 'outbound',
            'transfer' => 'transfer',
            default => $type,
        };

        $attributes = [
            'company_id' => $payload['company_id'] ?? null,
            'product_id' => $payload['product_id'] ?? null,
            'delivery_order_item_id' => $payload['delivery_order_item_id'] ?? null,
            'movement_type' => $movementType,
            'warehouse_from_id' => $payload['warehouse_from_id'] ?? (($type === 'outbound') ? ($payload['warehouse_id'] ?? null) : null),
            'warehouse_to_id' => $payload['warehouse_to_id'] ?? (($type === 'inbound') ? ($payload['warehouse_id'] ?? null) : null),
            'batch_number' => $payload['batch_number'] ?? null,
            'expiry_date' => $payload['expiry_date'] ?? null,
            'quantity' => $payload['quantity'] ?? 0,
            'fefo_fifo_rule' => $payload['fefo_fifo_rule'] ?? (($type === 'outbound') ? 'FEFO' : null),
            'reference_type' => $payload['reference_type'] ?? null,
            'reference_id' => $payload['reference_id'] ?? null,
        ];
        if (Schema::hasColumn('stock_movements', 'unit_id')) {
            $attributes['unit_id'] = $payload['unit_id'] ?? null;
        }
        if ($hasIdempotencyColumn) {
            $attributes['idempotency_key'] = $idempotencyKey;
        }

        StockMovement::create($attributes);
    }

    protected function convertToBaseQuantity(array $payload, float $quantity): float
    {
        $companyId = isset($payload['company_id']) ? (int) $payload['company_id'] : 0;
        $productId = isset($payload['product_id']) ? (int) $payload['product_id'] : 0;
        $unitId = isset($payload['unit_id']) ? (int) $payload['unit_id'] : null;

        if ($companyId <= 0 || $productId <= 0 || $unitId === null || $unitId <= 0) {
            return $quantity;
        }

        return $this->unitConversionService->convertToBase($companyId, $productId, $quantity, $unitId);
    }
}
