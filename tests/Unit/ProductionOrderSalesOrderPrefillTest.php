<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderItems;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Services\ProductionOrderSalesOrderPrefill;

it('prefills production order fields from an open sales order with finished goods line', function (): void {
    $fix = productionTenantFlowFixtures();
    if ($fix === null) {
        return;
    }

    $companyId = (int) $fix['company']->id;
    $currencyId = Currency::query()->where('company_id', $companyId)->value('id');
    if ($currencyId === null) {
        test()->markTestSkipped('No currency row for company.');

        return;
    }

    $nextNum = (int) (Order::withoutGlobalScopes()->where('company_id', $companyId)->max('order_number') ?? 0) + 1;

    $salesOrder = Order::withoutGlobalScopes()->create([
        'company_id' => $companyId,
        'client_id' => null,
        'estimate_id' => null,
        'order_date' => now()->toDateString(),
        'sub_total' => 100,
        'discount' => 0,
        'discount_type' => 'percent',
        'total' => 100,
        'due_amount' => 100,
        'status' => 'processing',
        'currency_id' => (int) $currencyId,
        'show_shipping_address' => 'no',
        'order_number' => $nextNum,
        'original_order_number' => (string) $nextNum,
    ]);

    OrderItems::withoutGlobalScopes()->create([
        'order_id' => $salesOrder->id,
        'item_name' => (string) $fix['fg']->name,
        'type' => 'item',
        'quantity' => 3000,
        'unit_price' => 1,
        'amount' => 3000,
        'product_id' => (int) $fix['fg']->id,
    ]);

    $bom = ProductionBom::query()
        ->where('company_id', $companyId)
        ->where('output_product_id', (int) $fix['fg']->id)
        ->orderByDesc('id')
        ->first();

    $prefill = app(ProductionOrderSalesOrderPrefill::class)->forSalesOrder((int) $salesOrder->id, $companyId);

    expect($prefill)->not->toBeNull()
        ->and($prefill['output_product_id'])->toBe((int) $fix['fg']->id)
        ->and((float) $prefill['planned_quantity'])->toBe(3000.0);

    if ($bom !== null) {
        expect($prefill['production_bom_id'])->toBe((int) $bom->id);
    }
});
