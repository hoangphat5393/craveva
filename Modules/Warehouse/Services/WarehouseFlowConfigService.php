<?php

namespace Modules\Warehouse\Services;

use Illuminate\Support\Facades\Schema;
use Modules\Warehouse\Entities\WarehouseCompanyFlowSetting;

/**
 * Resolves warehouse flow flags per company: DB row overrides merged onto config('warehouse.*').
 */
class WarehouseFlowConfigService
{
    /**
     * @return array{
     *     allow_negative_stock: bool,
     *     strict_unit_conversion: bool,
     *     inbound_from_purchase_order_delivered: bool,
     *     inbound_from_delivery_order_received: bool,
     *     sales_outbound_enabled: bool,
     *     sales_outbound_mode: string,
     *     ai_order_webhook_check_stock: bool
     * }
     */
    public function forCompany(?int $companyId): array
    {
        $base = $this->defaultsFromConfig();

        if ($companyId === null || $companyId <= 0) {
            return $base;
        }

        if (! Schema::hasTable('warehouse_company_flow_settings')) {
            return $base;
        }

        $row = WarehouseCompanyFlowSetting::query()->where('company_id', $companyId)->first();
        if ($row === null) {
            return $base;
        }

        return [
            'allow_negative_stock' => (bool) $row->allow_negative_stock,
            'strict_unit_conversion' => (bool) $row->strict_unit_conversion,
            'inbound_from_purchase_order_delivered' => (bool) $row->inbound_from_purchase_order_delivered,
            'inbound_from_delivery_order_received' => (bool) $row->inbound_from_delivery_order_received,
            'sales_outbound_enabled' => (bool) $row->sales_outbound_enabled,
            'sales_outbound_mode' => (string) $row->sales_outbound_mode,
            'ai_order_webhook_check_stock' => (bool) $row->ai_order_webhook_check_stock,
        ];
    }

    public function allowNegativeStock(?int $companyId): bool
    {
        return $this->forCompany($companyId)['allow_negative_stock'];
    }

    public function strictUnitConversion(?int $companyId): bool
    {
        return $this->forCompany($companyId)['strict_unit_conversion'];
    }

    public function inboundFromPurchaseOrderDelivered(?int $companyId): bool
    {
        return $this->forCompany($companyId)['inbound_from_purchase_order_delivered'];
    }

    public function inboundFromDeliveryOrderReceived(?int $companyId): bool
    {
        return $this->forCompany($companyId)['inbound_from_delivery_order_received'];
    }

    public function salesOutboundEnabled(?int $companyId): bool
    {
        return $this->forCompany($companyId)['sales_outbound_enabled'];
    }

    public function salesOutboundMode(?int $companyId): string
    {
        return $this->forCompany($companyId)['sales_outbound_mode'];
    }

    public function aiOrderWebhookCheckStock(?int $companyId): bool
    {
        return $this->forCompany($companyId)['ai_order_webhook_check_stock'];
    }

    /**
     * @return array{
     *     allow_negative_stock: bool,
     *     strict_unit_conversion: bool,
     *     inbound_from_purchase_order_delivered: bool,
     *     inbound_from_delivery_order_received: bool,
     *     sales_outbound_enabled: bool,
     *     sales_outbound_mode: string,
     *     ai_order_webhook_check_stock: bool
     * }
     */
    protected function defaultsFromConfig(): array
    {
        return [
            'allow_negative_stock' => (bool) config('warehouse.allow_negative_stock', false),
            'strict_unit_conversion' => (bool) config('warehouse.strict_unit_conversion', false),
            'inbound_from_purchase_order_delivered' => (bool) config('warehouse.inbound_from_purchase_order_delivered', true),
            'inbound_from_delivery_order_received' => (bool) config('warehouse.inbound_from_delivery_order_received', false),
            'sales_outbound_enabled' => (bool) config('warehouse.sales_outbound_enabled', true),
            'sales_outbound_mode' => (string) config('warehouse.sales_outbound_mode', 'shipment'),
            'ai_order_webhook_check_stock' => (bool) config('warehouse.ai_order_webhook_check_stock', true),
        ];
    }
}
