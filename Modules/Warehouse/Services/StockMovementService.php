<?php

namespace Modules\Warehouse\Services;

use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Warehouse\Concerns\ScopesWarehouseProductBatchQuery;
use Modules\Warehouse\Entities\WarehouseProductBatch;
use Modules\Warehouse\Entities\WarehouseProductStock;
use RuntimeException;

class StockMovementService
{
    use ScopesWarehouseProductBatchQuery;

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
        $qty = (float) ($payload['quantity'] ?? 0);
        if ($qty <= 0) {
            throw new RuntimeException('Inbound quantity must be greater than 0.');
        }

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
        $requested = (float) ($payload['quantity'] ?? 0);
        if ($requested <= 0) {
            throw new RuntimeException('Outbound quantity must be greater than 0.');
        }

        $rows = $this->resolveOutboundRows($payload);
        $available = (float) $rows->sum('quantity');
        $this->guardStockNotNegative($available, $requested, $allowNegativeStock);

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
            throw new RuntimeException('Transfer requires warehouse_from_id and warehouse_to_id.');
        }

        if ((int) $payload['warehouse_from_id'] === (int) $payload['warehouse_to_id']) {
            throw new RuntimeException('Transfer warehouses must be different.');
        }

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

    public function isNegativeStockAllowed(?bool $override = null): bool
    {
        if (! is_null($override)) {
            return $override;
        }

        return (bool) config('warehouse.allow_negative_stock', false);
    }

    public function guardStockNotNegative(float $available, float $requested, ?bool $allowNegativeStock = null): void
    {
        if ($this->isNegativeStockAllowed($allowNegativeStock)) {
            return;
        }

        if ($requested > $available) {
            throw new RuntimeException('Insufficient stock for outbound movement.');
        }
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
        $movementType = match ($type) {
            'inbound' => 'inbound',
            'outbound' => 'outbound',
            'transfer' => 'transfer',
            default => $type,
        };

        StockMovement::create([
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
        ]);
    }
}
