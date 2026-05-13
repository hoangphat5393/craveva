<?php

return [
    'name' => 'Warehouse',
    'allow_negative_stock' => env('WAREHOUSE_ALLOW_NEGATIVE_STOCK', false),
    /**
     * When true, reserve/outbound/inbound with non-base unit requires explicit product unit mapping.
     * When false, system falls back to quantity as-is if mapping is missing.
     */
    'strict_unit_conversion' => env('WAREHOUSE_STRICT_UNIT_CONVERSION', false),
    /**
     * Inbound stock when PO delivery_status becomes "delivered" (legacy / default on).
     * Set false if you only post receiving via Delivery Order (received) to avoid double-counting.
     */
    'inbound_from_purchase_order_delivered' => env('WAREHOUSE_INBOUND_FROM_PO_DELIVERED', true),
    /**
     * Inbound stock when Delivery Order (type inbound) status becomes "received".
     * Default false — enable when using DO as canonical GRN.
     */
    'inbound_from_delivery_order_received' => env('WAREHOUSE_INBOUND_FROM_DO_RECEIVED', false),

    /**
     * When true, finalized (non-draft) invoices post outbound stock via StockMovementService
     * and legacy PaymentObserver PurchaseStockAdjustment mutations are skipped.
     */
    'sales_outbound_enabled' => env('WAREHOUSE_SALES_OUTBOUND_ENABLED', true),

    /**
     * Sales outbound orchestration mode:
     * - "shipment": outbound is posted from SalesShipmentStockService at shipment shipped.
     * - "invoice": legacy behavior, outbound is posted from InvoiceWarehouseStockService.
     *
     * Keep this explicit to avoid double deduction between shipment and invoice.
     */
    'sales_outbound_mode' => env('WAREHOUSE_SALES_OUTBOUND_MODE', 'shipment'),

    /**
     * When true, AI inbound order create (REST POST /api/integrations/orders) validates line items with product_id
     * against WarehouseAvailabilityService (sellable qty) before creating the order.
     * Set false if integrations do not pass unit_id / stock yet.
     */
    'ai_order_webhook_check_stock' => env('WAREHOUSE_AI_ORDER_WEBHOOK_CHECK_STOCK', true),

    /**
     * Snapshot vs summed batch totals on the stock index reconciliation widget.
     */
    'inventory_reconciliation' => [
        'equality_epsilon' => (float) env('WAREHOUSE_INVENTORY_RECONCILIATION_EQUALITY_EPSILON', 0.0001),
        'warning_absolute_delta' => (float) env('WAREHOUSE_INVENTORY_RECONCILIATION_WARNING_ABSOLUTE_DELTA', 0.01),
    ],
];
