<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Production\Entities\ProductionBatch;
use Modules\Production\Entities\ProductionOrder;

uses(DatabaseTransactions::class);

it('returns validation errors when finished goods quantity exceeds planned tolerance without variance reason', function (): void {
    $fix = productionTenantFlowFixtures();
    if ($fix === null) {
        return;
    }

    $companyId = (int) $fix['company']->id;

    $order = ProductionOrder::query()->create([
        'company_id' => $companyId,
        'status' => ProductionOrder::STATUS_IN_PROGRESS,
        'output_product_id' => $fix['fg']->id,
        'production_bom_id' => null,
        'rm_warehouse_id' => $fix['rmWarehouse']->id,
        'fg_warehouse_id' => $fix['fgWarehouse']->id,
        'planned_quantity' => 2,
    ]);

    $batch = ProductionBatch::query()->create([
        'company_id' => $companyId,
        'production_order_id' => $order->id,
        'batch_code' => 'FG-VAL-' . uniqid(),
        'posted_consumptions_at' => now(),
    ]);

    $session = [
        'company' => $fix['company'],
        'multi_company_selected' => 1,
        'user_company_count' => 1,
    ];

    $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->from(route('production.batches.show', $batch))
        ->post(route('production.batches.outputs.store', $batch), [
            '_token' => csrf_token(),
            'quantity' => 10,
            'batch_number' => 'OUT-VAL-' . uniqid(),
            'warehouse_id' => $fix['fgWarehouse']->id,
            'variance_reason' => '',
        ])
        ->assertRedirect(route('production.batches.show', $batch))
        ->assertSessionHasErrors(['quantity', 'variance_reason']);

    expect(ProductionBatch::query()->find($batch->id)?->outputs()->count())->toBe(0);
});
