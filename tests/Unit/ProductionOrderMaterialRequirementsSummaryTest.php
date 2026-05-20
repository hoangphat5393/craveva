<?php

declare(strict_types=1);

use Modules\Production\Entities\ProductionBom;
use Modules\Production\Entities\ProductionBomItem;
use Modules\Production\Entities\ProductionOrder;
use Modules\Production\Services\ProductionOrderMaterialRequirementsSummary;

it('computes total required quantities from live BOM lines', function (): void {
    $fix = productionTenantFlowFixtures();
    if ($fix === null) {
        return;
    }

    $companyId = (int) $fix['company']->id;
    $rmProductId = (int) $fix['rm']->id;

    $bom = ProductionBom::query()
        ->where('company_id', $companyId)
        ->where('output_product_id', (int) $fix['fg']->id)
        ->orderByDesc('id')
        ->first();

    if ($bom === null) {
        test()->markTestSkipped('No BOM in tenant fixtures.');

        return;
    }

    ProductionBomItem::query()->updateOrCreate(
        [
            'company_id' => $companyId,
            'production_bom_id' => (int) $bom->id,
            'component_product_id' => $rmProductId,
        ],
        [
            'quantity' => 0.5,
            'waste_percent' => 0,
            'sort_order' => 0,
        ],
    );

    $order = ProductionOrder::query()->create([
        'company_id' => $companyId,
        'output_product_id' => (int) $fix['fg']->id,
        'production_bom_id' => (int) $bom->id,
        'rm_warehouse_id' => (int) $fix['rmWarehouse']->id,
        'fg_warehouse_id' => (int) $fix['fgWarehouse']->id,
        'planned_quantity' => 3000,
        'status' => ProductionOrder::STATUS_DRAFT,
    ]);

    $order->load(['bom.items.componentProduct.unit']);

    $rows = app(ProductionOrderMaterialRequirementsSummary::class)->forOrder($order);

    $rmRow = collect($rows)->firstWhere('component_product_id', $rmProductId);
    expect($rmRow)->not->toBeNull();
    expect((float) $rmRow['quantity_per_fg_unit'])->toBe(0.5);
    expect((float) $rmRow['waste_percent'])->toBe(0.0);
    expect((float) $rmRow['total_required'])->toBe(1500.0);
});

it('applies waste percent when calculating total required quantities', function (): void {
    $fix = productionTenantFlowFixtures();
    if ($fix === null) {
        return;
    }

    $companyId = (int) $fix['company']->id;
    $rmProductId = (int) $fix['rm']->id;

    $bom = ProductionBom::query()
        ->where('company_id', $companyId)
        ->where('output_product_id', (int) $fix['fg']->id)
        ->orderByDesc('id')
        ->first();

    if ($bom === null) {
        test()->markTestSkipped('No BOM in tenant fixtures.');

        return;
    }

    ProductionBomItem::query()->updateOrCreate(
        [
            'company_id' => $companyId,
            'production_bom_id' => (int) $bom->id,
            'component_product_id' => $rmProductId,
        ],
        [
            'quantity' => 0.5,
            'waste_percent' => 10,
            'sort_order' => 0,
        ],
    );

    $order = ProductionOrder::query()->create([
        'company_id' => $companyId,
        'output_product_id' => (int) $fix['fg']->id,
        'production_bom_id' => (int) $bom->id,
        'rm_warehouse_id' => (int) $fix['rmWarehouse']->id,
        'fg_warehouse_id' => (int) $fix['fgWarehouse']->id,
        'planned_quantity' => 3000,
        'status' => ProductionOrder::STATUS_DRAFT,
    ]);

    $order->load(['bom.items.componentProduct.unit']);

    $rows = app(ProductionOrderMaterialRequirementsSummary::class)->forOrder($order);
    $rmRow = collect($rows)->firstWhere('component_product_id', $rmProductId);

    expect((float) $rmRow['total_required'])->toBe(1650.0);
});
