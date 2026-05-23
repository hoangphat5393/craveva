<?php

declare(strict_types=1);

use App\Models\UnitType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Production\Entities\ProductionOrder;
use Modules\Production\Entities\ProductionOrderBomSnapshotItem;
use Modules\Production\Services\ProductionOrderMaterialRequirementsSummary;

it('defines unit relationship on bom snapshot item model', function (): void {
    $relation = (new ProductionOrderBomSnapshotItem)->unit();

    expect($relation)->toBeInstanceOf(BelongsTo::class);
    expect($relation->getRelated()::class)->toBe(UnitType::class);
});

it('loads bom snapshot material requirements without relation not found error', function (): void {
    $fix = productionTenantFlowFixtures();
    if ($fix === null) {
        return;
    }

    $companyId = (int) $fix['company']->id;
    $rmProductId = (int) $fix['rm']->id;

    $lineUnit = UnitType::query()->orderBy('id')->first();
    if ($lineUnit === null) {
        test()->markTestSkipped('No unit types in DB.');

        return;
    }

    $order = ProductionOrder::query()->create([
        'company_id' => $companyId,
        'output_product_id' => (int) $fix['fg']->id,
        'production_bom_id' => null,
        'rm_warehouse_id' => (int) $fix['rmWarehouse']->id,
        'fg_warehouse_id' => (int) $fix['fgWarehouse']->id,
        'planned_quantity' => 2,
        'status' => ProductionOrder::STATUS_RELEASED,
        'bom_snapshot_at' => now(),
        'bom_snapshot_planned_quantity' => 2,
    ]);

    ProductionOrderBomSnapshotItem::query()->create([
        'company_id' => $companyId,
        'production_order_id' => (int) $order->id,
        'component_product_id' => $rmProductId,
        'quantity_per_fg_unit' => 50,
        'waste_percent' => 0,
        'unit_id' => (int) $lineUnit->id,
        'sort_order' => 0,
    ]);

    $rows = app(ProductionOrderMaterialRequirementsSummary::class)->forOrder($order->fresh());

    expect($rows)->not->toBeEmpty();
    expect($rows[0]['unit_label'])->toBe($lineUnit->unit_type);
});
