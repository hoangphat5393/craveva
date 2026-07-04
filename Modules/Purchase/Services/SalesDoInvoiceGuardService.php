<?php

namespace Modules\Purchase\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Support\SalesDoRuntime;

class SalesDoInvoiceGuardService
{
    /**
     * @param  array<int|string, mixed>  $productIds
     * @param  array<int|string, mixed>  $quantities
     * @return array<int, array{product_id: int, requested: float, already_invoiced: float, shipped: float}>
     */
    public function exceededProducts(
        int $companyId,
        int $orderId,
        array $productIds,
        array $quantities,
        ?int $ignoreInvoiceId = null
    ): array {
        if ($companyId <= 0 || $orderId <= 0) {
            return [];
        }

        if (! $this->hasSalesDeliveryTables()) {
            return [];
        }

        $requested = $this->sumRequestedQuantities($productIds, $quantities);
        if ($requested === []) {
            return [];
        }

        $productIdList = array_keys($requested);
        $shipped = $this->sumShippedQuantities($companyId, $orderId, $productIdList);
        $invoiced = $this->sumInvoicedQuantities($companyId, $orderId, $productIdList, $ignoreInvoiceId);

        $exceeded = [];
        foreach ($requested as $productId => $requestedQty) {
            $alreadyInvoiced = (float) ($invoiced[$productId] ?? 0);
            $shippedQty = (float) ($shipped[$productId] ?? 0);

            if (($alreadyInvoiced + $requestedQty) > ($shippedQty + 0.0001)) {
                $exceeded[] = [
                    'product_id' => (int) $productId,
                    'requested' => $requestedQty,
                    'already_invoiced' => $alreadyInvoiced,
                    'shipped' => $shippedQty,
                ];
            }
        }

        return $exceeded;
    }

    /**
     * @param  array<int|string, mixed>  $productIds
     * @param  array<int|string, mixed>  $quantities
     * @return array<int, float>
     */
    private function sumRequestedQuantities(array $productIds, array $quantities): array
    {
        $requested = [];

        foreach ($productIds as $key => $productId) {
            $productId = (int) $productId;
            $quantity = (float) ($quantities[$key] ?? 0);

            if ($productId <= 0 || $quantity <= 0) {
                continue;
            }

            $requested[$productId] = ($requested[$productId] ?? 0) + $quantity;
        }

        return $requested;
    }

    private function hasSalesDeliveryTables(): bool
    {
        return Schema::hasTable(SalesDoRuntime::headerTable())
            && Schema::hasTable(SalesDoRuntime::itemTable());
    }

    /**
     * @param  array<int, int>  $productIds
     * @return array<int, float>
     */
    private function sumShippedQuantities(int $companyId, int $orderId, array $productIds): array
    {
        $headerTable = SalesDoRuntime::headerTable();
        $itemTable = SalesDoRuntime::itemTable();
        $itemForeignKey = SalesDoRuntime::itemForeignKey();

        if (! Schema::hasTable($headerTable) || ! Schema::hasTable($itemTable)) {
            return [];
        }

        return DB::table($itemTable)
            ->join($headerTable, $headerTable.'.id', '=', $itemTable.'.'.$itemForeignKey)
            ->where($headerTable.'.company_id', $companyId)
            ->where($headerTable.'.order_id', $orderId)
            ->whereIn($headerTable.'.status', ['shipped', 'delivered'])
            ->whereIn($itemTable.'.product_id', $productIds)
            ->select($itemTable.'.product_id', DB::raw('SUM('.$itemTable.'.quantity_shipped) as shipped_qty'))
            ->groupBy($itemTable.'.product_id')
            ->pluck('shipped_qty', 'product_id')
            ->map(fn ($qty) => (float) $qty)
            ->all();
    }

    /**
     * @param  array<int, int>  $productIds
     * @return array<int, float>
     */
    private function sumInvoicedQuantities(int $companyId, int $orderId, array $productIds, ?int $ignoreInvoiceId): array
    {
        $query = DB::table('invoice_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->where('invoices.company_id', $companyId)
            ->where('invoices.order_id', $orderId)
            ->whereIn('invoice_items.product_id', $productIds)
            ->whereNotIn('invoices.status', ['canceled', 'cancelled'])
            ->select('invoice_items.product_id', DB::raw('SUM(invoice_items.quantity) as invoiced_qty'))
            ->groupBy('invoice_items.product_id');

        if ($ignoreInvoiceId !== null && $ignoreInvoiceId > 0) {
            $query->where('invoices.id', '!=', $ignoreInvoiceId);
        }

        return $query
            ->pluck('invoiced_qty', 'product_id')
            ->map(fn ($qty) => (float) $qty)
            ->all();
    }
}
