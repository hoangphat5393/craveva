<?php

namespace Modules\Warehouse\Services;

use Illuminate\Support\Facades\DB;
use Modules\Warehouse\Concerns\ScopesWarehouseProductBatchQuery;
use Modules\Warehouse\Entities\StockReservation;
use Modules\Warehouse\Entities\WarehouseProductBatch;
use RuntimeException;

/**
 * Soft reservation against warehouse_product_batches.reserved_quantity + stock_reservations rows.
 * Wire to Sales Order / DO in a later phase; service is ready for programmatic use.
 */
class StockReservationService
{
    use ScopesWarehouseProductBatchQuery;

    public function __construct(
        protected WarehouseFlowPolicyService $flowPolicy,
        protected WarehouseUnitConversionService $unitConversionService
    ) {}

    /**
     * @param  array<string, mixed>  $payload  company_id, warehouse_id, product_id, quantity, batch_number?, expiry_date?, reference_type, reference_id
     */
    public function reserve(array $payload): StockReservation
    {
        return DB::transaction(function () use ($payload) {
            $qty = (float) ($payload['quantity'] ?? 0);
            if ($qty <= 0) {
                throw new RuntimeException('Reservation quantity must be greater than 0.');
            }

            $this->flowPolicy->assertSellableOutboundWarehouse((int) $payload['warehouse_id'], (string) ($payload['reference_type'] ?? 'reservation'));

            $qty = $this->convertToBaseQuantity($payload, $qty);
            $batch = $this->lockBatch($payload);
            $available = (float) $batch->quantity - (float) $batch->reserved_quantity;
            if ($available + 1e-9 < $qty) {
                throw new RuntimeException('Insufficient available quantity to reserve.');
            }

            $batch->reserved_quantity = (float) $batch->reserved_quantity + $qty;
            $batch->save();

            return StockReservation::create([
                'company_id' => $payload['company_id'] ?? null,
                'warehouse_id' => $payload['warehouse_id'],
                'product_id' => $payload['product_id'],
                'batch_number' => $payload['batch_number'] ?? null,
                'expiration_date' => $payload['expiry_date'] ?? null,
                'reserved_quantity' => $qty,
                'reference_type' => $payload['reference_type'] ?? null,
                'reference_id' => $payload['reference_id'] ?? null,
                'status' => 'active',
            ]);
        });
    }

    public function release(StockReservation $reservation): void
    {
        if ($reservation->status !== 'active') {
            return;
        }

        DB::transaction(function () use ($reservation) {
            $expiry = $reservation->expiration_date;
            $expiryStr = $expiry instanceof \DateTimeInterface ? $expiry->format('Y-m-d') : $expiry;

            $batch = $this->lockBatch([
                'warehouse_id' => $reservation->warehouse_id,
                'product_id' => $reservation->product_id,
                'batch_number' => $reservation->batch_number,
                'expiry_date' => $expiryStr,
            ]);

            $qty = (float) $reservation->reserved_quantity;
            $batch->reserved_quantity = max(0, (float) $batch->reserved_quantity - $qty);
            $batch->save();

            $reservation->status = 'released';
            $reservation->save();
        });
    }

    public function consume(StockReservation $reservation): void
    {
        if ($reservation->status !== 'active') {
            return;
        }

        DB::transaction(function () use ($reservation) {
            $expiry = $reservation->expiration_date;
            $expiryStr = $expiry instanceof \DateTimeInterface ? $expiry->format('Y-m-d') : $expiry;

            $batch = $this->lockBatch([
                'warehouse_id' => $reservation->warehouse_id,
                'product_id' => $reservation->product_id,
                'batch_number' => $reservation->batch_number,
                'expiry_date' => $expiryStr,
            ]);

            $qty = (float) $reservation->reserved_quantity;
            $batch->reserved_quantity = max(0, (float) $batch->reserved_quantity - $qty);
            $batch->save();

            $reservation->status = 'consumed';
            $reservation->save();
        });
    }

    public function hasActiveReservations(?string $referenceType, int $referenceId): bool
    {
        return StockReservation::query()
            ->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->where('status', 'active')
            ->where('reserved_quantity', '>', 0)
            ->exists();
    }

    public function releaseByReference(?string $referenceType, int $referenceId): void
    {
        DB::transaction(function () use ($referenceType, $referenceId) {
            $rows = StockReservation::query()
                ->where('reference_type', $referenceType)
                ->where('reference_id', $referenceId)
                ->where('status', 'active')
                ->lockForUpdate()
                ->get();

            foreach ($rows as $reservation) {
                $this->release($reservation);
            }
        });
    }

    public function consumeByReference(?string $referenceType, int $referenceId): void
    {
        DB::transaction(function () use ($referenceType, $referenceId) {
            $rows = StockReservation::query()
                ->where('reference_type', $referenceType)
                ->where('reference_id', $referenceId)
                ->where('status', 'active')
                ->lockForUpdate()
                ->get();

            foreach ($rows as $reservation) {
                $this->consume($reservation);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function lockBatch(array $payload): WarehouseProductBatch
    {
        $query = WarehouseProductBatch::query()
            ->lockForUpdate()
            ->where('warehouse_id', $payload['warehouse_id'])
            ->where('product_id', $payload['product_id']);
        $this->applyBatchIdentityToQuery($query, $payload);

        $row = $query->first();
        if (! $row) {
            throw new RuntimeException('Batch row not found for reservation.');
        }

        return $row;
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
