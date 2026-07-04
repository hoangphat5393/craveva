<?php

namespace App\Services\Company;

/**
 * Ordered purge steps — see FUNC_LOGIC/COMPANY_TRANSACTION_PURGE_GUIDE_VI.md
 *
 * @return list<CompanyTransactionPurgeStep>
 */
final class CompanyTransactionPurgePlan
{
    public static function steps(bool $includeBoms = false): array
    {
        $steps = [
            // Phase A — inventory / warehouse
            new CompanyTransactionPurgeStep('A', 'stock_movement_commands'),
            new CompanyTransactionPurgeStep('A', 'stock_reservations'),
            new CompanyTransactionPurgeStep('A', 'stock_movements'),
            new CompanyTransactionPurgeStep('A', 'warehouse_product_batches'),
            new CompanyTransactionPurgeStep('A', 'warehouse_product_stock', 'child_of_company', 'warehouse_id', 'warehouses', 'id'),
            new CompanyTransactionPurgeStep('A', 'invoice_warehouse_stock_postings'),
            new CompanyTransactionPurgeStep('A', 'warehouse_sync_reconciliation_logs'),
            new CompanyTransactionPurgeStep('A', 'purchase_inventory_files', 'child_of_company', 'inventory_id', 'purchase_inventory_adjustment', 'id'),
            new CompanyTransactionPurgeStep('A', 'purchase_stock_adjustments'),
            new CompanyTransactionPurgeStep('A', 'purchase_inventory_adjustment'),

            // Phase B — production runtime (keeps production_boms / production_bom_items)
            new CompanyTransactionPurgeStep('B', 'production_batch_consumptions'),
            new CompanyTransactionPurgeStep('B', 'production_batch_outputs'),
            new CompanyTransactionPurgeStep('B', 'production_batches'),
            new CompanyTransactionPurgeStep('B', 'production_order_bom_snapshot_items'),
            new CompanyTransactionPurgeStep('B', 'production_rework_orders'),
            new CompanyTransactionPurgeStep('B', 'production_orders'),

            // Phase C — purchase / GRN
            new CompanyTransactionPurgeStep('C', 'delivery_order_items', 'child_of_company', 'delivery_order_id', 'delivery_orders', 'id'),
            new CompanyTransactionPurgeStep('C', 'delivery_orders'),
            new CompanyTransactionPurgeStep('C', 'grn_items', 'child_of_company', 'grn_id', 'grns', 'id'),
            new CompanyTransactionPurgeStep('C', 'grns'),
            new CompanyTransactionPurgeStep('C', 'purchase_payment_histories', 'child_of_company', 'purchase_payment_id', 'purchase_vendor_payments', 'id'),
            new CompanyTransactionPurgeStep('C', 'purchase_payment_bills', 'child_of_company', 'purchase_vendor_payment_id', 'purchase_vendor_payments', 'id'),
            new CompanyTransactionPurgeStep('C', 'purchase_vendor_payments'),
            new CompanyTransactionPurgeStep('C', 'purchase_bill_histories', 'child_of_company', 'purchase_bill_id', 'purchase_bills', 'id'),
            new CompanyTransactionPurgeStep('C', 'purchase_bills'),
            new CompanyTransactionPurgeStep('C', 'purchase_order_histories', 'child_of_company', 'purchase_order_id', 'purchase_orders', 'id'),
            new CompanyTransactionPurgeStep('C', 'purchase_order_files', 'child_of_company', 'purchase_order_id', 'purchase_orders', 'id'),
            new CompanyTransactionPurgeStep('C', 'purchase_item_images', 'child_of_company', 'purchase_item_id', 'purchase_items', 'id'),
            new CompanyTransactionPurgeStep('C', 'purchase_item_taxes', 'child_of_company', 'purchase_item_id', 'purchase_items', 'id'),
            new CompanyTransactionPurgeStep('C', 'purchase_items', 'child_of_company', 'purchase_order_id', 'purchase_orders', 'id'),
            new CompanyTransactionPurgeStep('C', 'purchase_orders'),
            new CompanyTransactionPurgeStep('C', 'purchase_vendor_items', 'child_of_company', 'credit_id', 'purchase_vendor_credits', 'id'),
            new CompanyTransactionPurgeStep('C', 'purchase_vendor_credit_histories', 'child_of_company', 'purchase_credit_id', 'purchase_vendor_credits', 'id'),
            new CompanyTransactionPurgeStep('C', 'purchase_vendor_credits'),
            new CompanyTransactionPurgeStep('C', 'purchase_inventory_histories', 'child_of_company', 'inventory_id', 'purchase_inventory_adjustment', 'id'),

            // Phase D — sales DO
            new CompanyTransactionPurgeStep('D', 'sales_shipment_items', 'child_of_company', 'sales_shipment_id', 'sales_shipments', 'id'),
            new CompanyTransactionPurgeStep('D', 'sales_shipments'),
            new CompanyTransactionPurgeStep('D', 'sales_do_items', 'child_of_company', 'sales_do_id', 'sales_dos', 'id'),
            new CompanyTransactionPurgeStep('D', 'sales_dos'),

            // Phase E — payments / invoices / credit notes
            new CompanyTransactionPurgeStep('E', 'invoice_payment_details'),
            new CompanyTransactionPurgeStep('E', 'payments'),
            new CompanyTransactionPurgeStep('E', 'invoice_items', 'child_of_company', 'invoice_id', 'invoices', 'id'),
            new CompanyTransactionPurgeStep('E', 'invoice_files', 'child_of_company', 'invoice_id', 'invoices', 'id'),
            new CompanyTransactionPurgeStep('E', 'invoices'),
            new CompanyTransactionPurgeStep('E', 'credit_note_items', 'child_of_company', 'credit_note_id', 'credit_notes', 'id'),
            new CompanyTransactionPurgeStep('E', 'credit_notes'),

            // Phase F — sales orders
            new CompanyTransactionPurgeStep('F', 'order_items', 'child_of_company', 'order_id', 'orders', 'id'),
            new CompanyTransactionPurgeStep('F', 'orders'),
            new CompanyTransactionPurgeStep('F', 'order_carts', 'child_of_company', 'client_id', 'users', 'id'),

            // Phase G — estimates (not estimate_templates)
            new CompanyTransactionPurgeStep('G', 'estimate_requests'),
            new CompanyTransactionPurgeStep('G', 'estimate_approval_events', 'child_of_company', 'estimate_id', 'estimates', 'id'),
            new CompanyTransactionPurgeStep('G', 'estimate_bom_lines', 'child_of_company', 'estimate_id', 'estimates', 'id'),
            new CompanyTransactionPurgeStep('G', 'estimate_items', 'child_of_company', 'estimate_id', 'estimates', 'id'),
            new CompanyTransactionPurgeStep('G', 'accept_estimates', 'child_of_company', 'estimate_id', 'estimates', 'id'),
            new CompanyTransactionPurgeStep('G', 'estimates'),
        ];

        if ($includeBoms) {
            $steps[] = new CompanyTransactionPurgeStep('I', 'production_bom_items', 'child_of_company', 'production_bom_id', 'production_boms', 'id');
            $steps[] = new CompanyTransactionPurgeStep('I', 'production_boms');
        }

        return $steps;
    }
}
