<?php

namespace Modules\Warehouse\Services;

use Illuminate\Support\Facades\Log;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Exceptions\WarehouseBusinessException;

class WarehouseFlowPolicyService
{
    /**
     * Warehouses that can be sold from (reservation/outbound sales).
     *
     * @return array<int, string>
     */
    public function sellableWarehouseTypes(): array
    {
        return ['normal'];
    }

    public function isSellableWarehouseType(?string $warehouseType): bool
    {
        return in_array((string) $warehouseType, $this->sellableWarehouseTypes(), true);
    }

    public function assertSellableOutboundWarehouse(int $warehouseId, ?string $referenceType = null): void
    {
        $warehouse = Warehouse::query()->find($warehouseId);
        if (! $warehouse) {
            throw new WarehouseBusinessException(__('warehouse::app.err_warehouse_not_in_company'), [
                'warehouse_id' => $warehouseId,
            ]);
        }

        $warehouseType = (string) ($warehouse->warehouse_type ?? 'normal');
        if ($this->isSellableWarehouseType($warehouseType)) {
            return;
        }

        throw new WarehouseBusinessException(__('warehouse::app.err_outbound_forbidden_warehouse_type', [
            'warehouse' => $warehouse->name,
            'warehouse_type' => $warehouseType,
            'reference' => (string) $referenceType,
        ]), [
            'warehouse_id' => $warehouseId,
            'warehouse_type' => $warehouseType,
            'reference_type' => $referenceType,
        ]);
    }

    public function assertInboundSourceAllowed(string $source): void
    {
        $poEnabled = (bool) config('warehouse.inbound_from_purchase_order_delivered', true);
        $doEnabled = (bool) config('warehouse.inbound_from_delivery_order_received', false);
        if ($poEnabled && $doEnabled) {
            Log::warning('Warehouse inbound configuration conflict detected', [
                'source_attempted' => $source,
                'inbound_from_purchase_order_delivered' => $poEnabled,
                'inbound_from_delivery_order_received' => $doEnabled,
            ]);

            throw new WarehouseBusinessException(__('warehouse::app.err_inbound_canonical_conflict'));
        }
    }

    public function outboundMode(): string
    {
        return (string) config('warehouse.sales_outbound_mode', 'shipment');
    }

    public function assertOutboundConfigurationValid(): void
    {
        $mode = $this->outboundMode();
        if (in_array($mode, ['invoice', 'shipment'], true)) {
            return;
        }

        Log::warning('Warehouse outbound configuration invalid mode', [
            'sales_outbound_mode' => $mode,
        ]);

        throw new WarehouseBusinessException(__('warehouse::app.err_outbound_mode_invalid', [
            'mode' => $mode,
        ]));
    }
}
