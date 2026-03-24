<?php

namespace Modules\Warehouse\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Exact batch identity: warehouse + product + (batch_number or null) + (expiry_date or null expiration).
 * Used when locking a single batch row for inbound merge or reservation — not for multi-batch FEFO outbound.
 */
trait ScopesWarehouseProductBatchQuery
{
    protected function applyBatchIdentityToQuery(Builder $query, array $payload): void
    {
        if (array_key_exists('batch_number', $payload) && ! is_null($payload['batch_number'])) {
            $query->where('batch_number', $payload['batch_number']);
        } else {
            $query->whereNull('batch_number');
        }

        if (array_key_exists('expiry_date', $payload) && ! is_null($payload['expiry_date'])) {
            $query->whereDate('expiration_date', $payload['expiry_date']);
        } else {
            $query->whereNull('expiration_date');
        }
    }
}
