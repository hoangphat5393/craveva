<?php

declare(strict_types=1);

use App\Models\InvoiceSetting;
use App\Support\CompanyDocumentTerms;
use Modules\Purchase\Entities\PurchaseSetting;

it('resolves sale order terms with invoice terms fallback', function (): void {
    $invoiceSetting = new InvoiceSetting;
    $invoiceSetting->order_terms = ' SO terms ';
    $invoiceSetting->invoice_terms = 'Invoice terms';

    expect(CompanyDocumentTerms::resolveSaleOrderTerms($invoiceSetting))->toBe('SO terms');
});

it('falls back to invoice terms when order terms empty', function (): void {
    $invoiceSetting = new InvoiceSetting;
    $invoiceSetting->order_terms = '';
    $invoiceSetting->invoice_terms = 'Invoice terms';

    expect(CompanyDocumentTerms::resolveSaleOrderTerms($invoiceSetting))->toBe('Invoice terms');
});

it('resolves grn terms from shared purchase terms', function (): void {
    $purchaseSetting = new PurchaseSetting;
    $purchaseSetting->purchase_terms = ' Purchase terms ';

    expect(CompanyDocumentTerms::resolveGrnTerms($purchaseSetting))->toBe('Purchase terms');
});

it('resolves purchase order terms from purchase settings', function (): void {
    $purchaseSetting = new PurchaseSetting;
    $purchaseSetting->purchase_terms = ' Purchase terms ';

    expect(CompanyDocumentTerms::resolvePurchaseOrderTerms($purchaseSetting))->toBe('Purchase terms');
});
