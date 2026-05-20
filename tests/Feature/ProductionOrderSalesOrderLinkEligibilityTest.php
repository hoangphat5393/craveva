<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Production\Entities\ProductionBom;

uses(DatabaseTransactions::class);

it('rejects storing a draft production order linked to a completed sales order', function (): void {
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

    $closedSalesOrder = Order::withoutGlobalScopes()->create([
        'company_id' => $companyId,
        'client_id' => null,
        'order_date' => now()->toDateString(),
        'sub_total' => 1,
        'discount' => 0,
        'discount_type' => 'percent',
        'total' => 1,
        'due_amount' => 1,
        'status' => 'completed',
        'currency_id' => (int) $currencyId,
        'show_shipping_address' => 'no',
        'order_number' => $nextNum,
        'original_order_number' => (string) $nextNum,
    ]);

    $bom = ProductionBom::query()
        ->where('company_id', $companyId)
        ->where('output_product_id', (int) $fix['fg']->id)
        ->orderByDesc('id')
        ->first();

    if ($bom === null) {
        test()->markTestSkipped('No BOM for FG product in tenant fixtures.');

        return;
    }

    $response = $this->actingAs($fix['userAuth'], 'web')
        ->withSession([
            'company' => $fix['company'],
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->post(route('production.orders.store'), [
            '_token' => csrf_token(),
            'output_product_id' => (int) $fix['fg']->id,
            'production_bom_id' => (int) $bom->id,
            'rm_warehouse_id' => (int) $fix['rmWarehouse']->id,
            'fg_warehouse_id' => (int) $fix['fgWarehouse']->id,
            'planned_quantity' => 1,
            'sales_order_id' => (int) $closedSalesOrder->id,
            'project_id' => null,
        ]);

    $response->assertSessionHasErrors('sales_order_id');
});

it('lists only open sales orders on the production order create form', function (): void {
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

    $closedSalesOrder = Order::withoutGlobalScopes()->create([
        'company_id' => $companyId,
        'client_id' => null,
        'order_date' => now()->toDateString(),
        'sub_total' => 1,
        'discount' => 0,
        'discount_type' => 'percent',
        'total' => 1,
        'due_amount' => 1,
        'status' => 'completed',
        'currency_id' => (int) $currencyId,
        'show_shipping_address' => 'no',
        'order_number' => $nextNum,
        'original_order_number' => (string) $nextNum,
    ]);

    $openSalesOrder = Order::withoutGlobalScopes()->create([
        'company_id' => $companyId,
        'client_id' => null,
        'order_date' => now()->toDateString(),
        'sub_total' => 1,
        'discount' => 0,
        'discount_type' => 'percent',
        'total' => 1,
        'due_amount' => 1,
        'status' => 'pending',
        'currency_id' => (int) $currencyId,
        'show_shipping_address' => 'no',
        'order_number' => $nextNum + 1,
        'original_order_number' => (string) ($nextNum + 1),
    ]);

    $html = $this->actingAs($fix['userAuth'], 'web')
        ->withSession([
            'company' => $fix['company'],
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->get(route('production.orders.create'))
        ->assertSuccessful()
        ->getContent();

    expect($html)->toContain('#'.$openSalesOrder->id.' —')
        ->and($html)->not->toContain('#'.$closedSalesOrder->id.' —')
        ->and($html)->toContain(__('modules.invoices.pending'));
});

it('prefills sales order when opening create form with sales_order_id query', function (): void {
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

    $openSalesOrder = Order::withoutGlobalScopes()->create([
        'company_id' => $companyId,
        'client_id' => null,
        'order_date' => now()->toDateString(),
        'sub_total' => 1,
        'discount' => 0,
        'discount_type' => 'percent',
        'total' => 1,
        'due_amount' => 1,
        'status' => 'processing',
        'currency_id' => (int) $currencyId,
        'show_shipping_address' => 'no',
        'order_number' => $nextNum,
        'original_order_number' => (string) $nextNum,
    ]);

    $html = $this->actingAs($fix['userAuth'], 'web')
        ->withSession([
            'company' => $fix['company'],
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->get(route('production.orders.create', ['sales_order_id' => $openSalesOrder->id]))
        ->assertSuccessful()
        ->getContent();

    expect($html)->toContain('value="'.$openSalesOrder->id.'" selected');
});
