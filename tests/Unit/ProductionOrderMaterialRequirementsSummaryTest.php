<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\UnitType;
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

    $order->load(['bom.items.componentProduct.unit', 'bom.items.unit']);

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

    $order->load(['bom.items.componentProduct.unit', 'bom.items.unit']);

    $rows = app(ProductionOrderMaterialRequirementsSummary::class)->forOrder($order);
    $rmRow = collect($rows)->firstWhere('component_product_id', $rmProductId);

    expect((float) $rmRow['total_required'])->toBe(1650.0);
});

it('shows BOM line unit on quantity per FG and product base unit on totals', function (): void {
    $fix = productionTenantFlowFixtures();
    if ($fix === null) {
        return;
    }

    $units = UnitType::query()->orderBy('id')->get();
    $lineUnit = $units->first(static fn (UnitType $u): bool => strtolower((string) $u->unit_type) !== 'kg');
    $baseUnit = $units->first(static fn (UnitType $u): bool => strtolower((string) $u->unit_type) === 'kg')
        ?? $units->first(static fn (UnitType $u): bool => $lineUnit === null || (int) $u->id !== (int) $lineUnit->id);

    if ($lineUnit === null || $baseUnit === null || (int) $lineUnit->id === (int) $baseUnit->id) {
        test()->markTestSkipped('Need two distinct unit types (e.g. g and kg) in DB.');

        return;
    }

    $companyId = (int) $fix['company']->id;
    $rmProductId = (int) $fix['rm']->id;

    Product::withoutGlobalScopes()
        ->whereKey($rmProductId)
        ->update(['unit_id' => (int) $baseUnit->id]);

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
            'quantity' => 50,
            'waste_percent' => 0,
            'unit_id' => (int) $lineUnit->id,
            'sort_order' => 0,
        ],
    );

    $order = ProductionOrder::query()->create([
        'company_id' => $companyId,
        'output_product_id' => (int) $fix['fg']->id,
        'production_bom_id' => (int) $bom->id,
        'rm_warehouse_id' => (int) $fix['rmWarehouse']->id,
        'fg_warehouse_id' => (int) $fix['fgWarehouse']->id,
        'planned_quantity' => 2,
        'status' => ProductionOrder::STATUS_DRAFT,
    ]);

    $order->load(['bom.items.componentProduct.unit', 'bom.items.unit']);

    $rows = app(ProductionOrderMaterialRequirementsSummary::class)->forOrder($order);
    $rmRow = collect($rows)->firstWhere('component_product_id', $rmProductId);

    expect($rmRow)->not->toBeNull();
    expect((float) $rmRow['quantity_per_fg_unit'])->toBe(50.0);
    expect($rmRow['unit_label'])->toBe($lineUnit->unit_type);
    expect($rmRow['unit_label_base'])->toBe($baseUnit->unit_type);
    expect($rmRow['unit_label'])->not->toBe($rmRow['unit_label_base']);
});
