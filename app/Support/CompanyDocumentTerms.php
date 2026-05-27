<?php

namespace App\Support;

use App\Models\InvoiceSetting;
use Modules\Purchase\Entities\PurchaseSetting;

class CompanyDocumentTerms
{
    public static function resolveSaleOrderTerms(?InvoiceSetting $invoiceSetting): string
    {
        if ($invoiceSetting === null) {
            return '';
        }

        $orderTerms = trim((string) ($invoiceSetting->order_terms ?? ''));

        if ($orderTerms !== '') {
            return $orderTerms;
        }

        return trim((string) ($invoiceSetting->invoice_terms ?? ''));
    }

    public static function resolveGrnTerms(?PurchaseSetting $purchaseSetting): string
    {
        if ($purchaseSetting === null) {
            return '';
        }

        $grnTerms = trim((string) ($purchaseSetting->grn_terms ?? ''));

        if ($grnTerms !== '') {
            return $grnTerms;
        }

        return trim((string) ($purchaseSetting->purchase_terms ?? ''));
    }

    public static function resolvePurchaseOrderTerms(?PurchaseSetting $purchaseSetting): string
    {
        if ($purchaseSetting === null) {
            return '';
        }

        return trim((string) ($purchaseSetting->purchase_terms ?? ''));
    }
}
