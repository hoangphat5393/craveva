<?php

return [
    'name' => 'Warehouse',
    'allow_negative_stock' => env('WAREHOUSE_ALLOW_NEGATIVE_STOCK', false),
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
    'sales_outbound_enabled' => env('WAREHOUSE_SALES_OUTBOUND_ENABLED', false),

    /**
     * Sales outbound orchestration mode:
     * - "shipment": outbound is posted from SalesShipmentStockService at shipment shipped.
     * - "invoice": legacy behavior, outbound is posted from InvoiceWarehouseStockService.
     *
     * Keep this explicit to avoid double deduction between shipment and invoice.
     */
    'sales_outbound_mode' => env('WAREHOUSE_SALES_OUTBOUND_MODE', 'invoice'),
];
