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

it('resolves grn terms with purchase terms fallback', function (): void {
    $purchaseSetting = new PurchaseSetting;
    $purchaseSetting->grn_terms = ' GRN terms ';
    $purchaseSetting->purchase_terms = 'PO terms';

    expect(CompanyDocumentTerms::resolveGrnTerms($purchaseSetting))->toBe('GRN terms');
});

it('falls back to purchase terms when grn terms empty', function (): void {
    $purchaseSetting = new PurchaseSetting;
    $purchaseSetting->grn_terms = '';
    $purchaseSetting->purchase_terms = 'PO terms';

    expect(CompanyDocumentTerms::resolveGrnTerms($purchaseSetting))->toBe('PO terms');
});
