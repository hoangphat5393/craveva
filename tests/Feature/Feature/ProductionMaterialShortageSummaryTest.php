<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Entities\ProductionBomItem;
use Modules\Production\Entities\ProductionOrder;
use Modules\Production\Services\ProductionMaterialSummaryService;
use Modules\Production\Services\ProductionOrderMaterialRequirementsSummary;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseProductBatch;
use Modules\Warehouse\Entities\WarehouseProductStock;

uses(DatabaseTransactions::class);

it('renders the material shortage summary screen with status and warehouse filters', function (): void {
    $fix = productionTenantFlowFixtures();
    if ($fix === null) {
        return;
    }

    $session = [
        'company' => $fix['company'],
        'multi_company_selected' => 1,
        'user_company_count' => 1,
    ];

    $content = $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->get(route('production.material-shortages.index'))
        ->assertSuccessful()
        ->getContent();

    expect($content)->toContain(__('production::app.materialShortageSummary'));
    expect($content)->toContain('production-material-shortages-table');
    expect($content)->toContain('id="production-material-shortages-status-filter"');
    expect($content)->toContain('id="production-material-shortages-warehouse-filter"');
    expect($content)->toContain('value="' . ProductionMaterialSummaryService::SCOPE_ALL . '"');
    expect($content)->toContain(__('production::app.materialShortageStatusScopes.all'));
});

it('maps material shortage status scopes to production order statuses', function (): void {
    $service = app(ProductionMaterialSummaryService::class);

    expect($service->statusesForScope(null))->toEqual([
        ProductionOrder::STATUS_DRAFT,
        ProductionOrder::STATUS_RELEASED,
        ProductionOrder::STATUS_IN_PROGRESS,
    ]);
    expect($service->statusesForScope(ProductionMaterialSummaryService::SCOPE_ACTIVE))->toEqual([
        ProductionOrder::STATUS_RELEASED,
        ProductionOrder::STATUS_IN_PROGRESS,
    ]);
    expect($service->statusesForScope(ProductionMaterialSummaryService::SCOPE_DRAFT))->toEqual([ProductionOrder::STATUS_DRAFT]);
    expect($service->statusesForScope(ProductionMaterialSummaryService::SCOPE_ALL))->toEqual([
        ProductionOrder::STATUS_DRAFT,
        ProductionOrder::STATUS_RELEASED,
        ProductionOrder::STATUS_IN_PROGRESS,
    ]);
    expect($service->normalizeStatusScope('invalid'))->toBe(ProductionMaterialSummaryService::DEFAULT_STATUS_SCOPE);
});

it('aggregates shortages across draft production orders when scope is draft', function (): void {
    $fix = productionTenantFlowFixtures();
    if ($fix === null) {
        return;
    }

    $session = [
        'company' => $fix['company'],
        'multi_company_selected' => 1,
        'user_company_count' => 1,
    ];

    $headers = [
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'application/json',
    ];

    $bom = ProductionBom::query()->create([
        'company_id' => (int) $fix['company']->id,
        'output_product_id' => (int) $fix['fg']->id,
        'version' => 'summary-' . uniqid(),
        'code' => 'summary-bom',
        'is_default' => false,
        'created_by' => $fix['user']->id,
        'updated_by' => $fix['user']->id,
    ]);

    ProductionBomItem::query()->create([
        'company_id' => (int) $fix['company']->id,
        'production_bom_id' => $bom->id,
        'component_product_id' => (int) $fix['rm']->id,
        'quantity' => 1,
        'sort_order' => 0,
    ]);

    $summaryWarehouse = Warehouse::query()->create([
        'company_id' => (int) $fix['company']->id,
        'name' => 'Summary RM Warehouse ' . uniqid(),
        'code' => 'SUM-RM-' . uniqid(),
        'warehouse_type' => 'normal',
        'status' => 'active',
    ]);

    WarehouseProductStock::query()->updateOrCreate(
        [
            'warehouse_id' => (int) $summaryWarehouse->id,
            'product_id' => (int) $fix['rm']->id,
        ],
        [
            'quantity' => 20,
        ],
    );

    WarehouseProductBatch::query()->create([
        'company_id' => (int) $fix['company']->id,
        'warehouse_id' => (int) $summaryWarehouse->id,
        'product_id' => (int) $fix['rm']->id,
        'batch_number' => 'RES-' . uniqid(),
        'quantity' => 20,
        'reserved_quantity' => 5,
    ]);

    $firstOrder = createDraftProductionOrderForShortageSummary($fix, $bom, 20, (int) $summaryWarehouse->id);
    $secondOrder = createDraftProductionOrderForShortageSummary($fix, $bom, 30, (int) $summaryWarehouse->id);

    createReleasedProductionOrderWithSnapshot($fix, $bom, 100, (int) $summaryWarehouse->id);

    $requirementsSummary = app(ProductionOrderMaterialRequirementsSummary::class);
    $expectedTotalRequired = (float) ($requirementsSummary->demandRowsForOrder($firstOrder)[0]['total_required'] ?? 0)
        + (float) ($requirementsSummary->demandRowsForOrder($secondOrder)[0]['total_required'] ?? 0);
    $expectedAvailable = 15.0;
    $expectedShortage = max($expectedTotalRequired - $expectedAvailable, 0.0);

    $datatableResponse = $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->withHeaders($headers)
        ->get(route('production.material-shortages.index', productionDatatableRequest([
            ['data' => 'component_name', 'name' => 'component_name'],
            ['data' => 'rm_warehouse_name', 'name' => 'rm_warehouse_name'],
            ['data' => 'total_required', 'name' => 'total_required'],
            ['data' => 'available_stock', 'name' => 'available_stock'],
            ['data' => 'shortage_to_procure', 'name' => 'shortage_to_procure'],
            ['data' => 'unit_label_base', 'name' => 'unit_label_base'],
            ['data' => 'affected_orders_count', 'name' => 'affected_orders_count'],
            ['data' => 'action', 'searchable' => false, 'orderable' => false],
        ], [
            'status_scope' => ProductionMaterialSummaryService::SCOPE_DRAFT,
            'warehouse_id' => (string) $summaryWarehouse->id,
            'material_id' => (string) $fix['rm']->id,
            'only_shortage' => 1,
        ], 4, 'desc')));

    $datatableResponse->assertSuccessful();

    $rows = $datatableResponse->json('data');

    expect($rows)->toHaveCount(1);
    expect((string) $rows[0]['affected_orders_count'])->toBe('2');
    expect((string) $rows[0]['total_required'])->toBe(formatProductionQuantityForAssertion($expectedTotalRequired));
    expect((string) $rows[0]['shortage_to_procure'])->toBe(formatProductionQuantityForAssertion($expectedShortage));

    $detailContent = $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->get(route('production.material-shortages.orders', [
            'material_id' => (int) $fix['rm']->id,
            'warehouse_id' => (int) $summaryWarehouse->id,
            'status_scope' => ProductionMaterialSummaryService::SCOPE_DRAFT,
        ]))
        ->assertSuccessful()
        ->getContent();

    expect($detailContent)->toContain((string) $firstOrder->id);
    expect($detailContent)->toContain((string) $secondOrder->id);
    expect($detailContent)->not->toContain(__('production::app.statusLabels.released'));
});

it('excludes draft orders from default active scope aggregation', function (): void {
    $fix = productionTenantFlowFixtures();
    if ($fix === null) {
        return;
    }

    $bom = ProductionBom::query()->create([
        'company_id' => (int) $fix['company']->id,
        'output_product_id' => (int) $fix['fg']->id,
        'version' => 'active-' . uniqid(),
        'code' => 'active-bom',
        'is_default' => false,
        'created_by' => $fix['user']->id,
        'updated_by' => $fix['user']->id,
    ]);

    ProductionBomItem::query()->create([
        'company_id' => (int) $fix['company']->id,
        'production_bom_id' => $bom->id,
        'component_product_id' => (int) $fix['rm']->id,
        'quantity' => 1,
        'sort_order' => 0,
    ]);

    $warehouse = Warehouse::query()->create([
        'company_id' => (int) $fix['company']->id,
        'name' => 'Active scope WH ' . uniqid(),
        'code' => 'ACT-' . uniqid(),
        'warehouse_type' => 'normal',
        'status' => 'active',
    ]);

    WarehouseProductBatch::query()->create([
        'company_id' => (int) $fix['company']->id,
        'warehouse_id' => (int) $warehouse->id,
        'product_id' => (int) $fix['rm']->id,
        'batch_number' => 'B-' . uniqid(),
        'quantity' => 100,
        'reserved_quantity' => 0,
    ]);

    createDraftProductionOrderForShortageSummary($fix, $bom, 50, (int) $warehouse->id);
    $released = createReleasedProductionOrderWithSnapshot($fix, $bom, 10, (int) $warehouse->id);

    $service = app(ProductionMaterialSummaryService::class);
    $rows = $service->summaries((int) $fix['company']->id, [
        'status_scope' => ProductionMaterialSummaryService::SCOPE_ACTIVE,
        'warehouse_id' => (int) $warehouse->id,
        'material_id' => (int) $fix['rm']->id,
        'only_shortage' => false,
    ]);

    expect($rows)->toHaveCount(1);
    expect($rows[0]['affected_order_ids'])->toBe([(int) $released->id]);
});

/**
 * @param  array{company: Company, user: User, fg: Product, rm: Product, rmWarehouse: Warehouse, fgWarehouse: Warehouse}  $fix
 */
function createDraftProductionOrderForShortageSummary(array $fix, ProductionBom $bom, float $plannedQuantity, int $rmWarehouseId): ProductionOrder
{
    return ProductionOrder::query()->create([
        'company_id' => (int) $fix['company']->id,
        'output_product_id' => (int) $fix['fg']->id,
        'production_bom_id' => $bom->id,
        'rm_warehouse_id' => $rmWarehouseId,
        'fg_warehouse_id' => (int) $fix['fgWarehouse']->id,
        'planned_quantity' => $plannedQuantity,
        'status' => ProductionOrder::STATUS_DRAFT,
        'created_by' => $fix['user']->id,
        'updated_by' => $fix['user']->id,
    ]);
}

/**
 * @param  array{company: Company, user: User, fg: Product, rm: Product, rmWarehouse: Warehouse, fgWarehouse: Warehouse}  $fix
 */
function createReleasedProductionOrderWithSnapshot(array $fix, ProductionBom $bom, float $plannedQuantity, int $rmWarehouseId): ProductionOrder
{
    $order = ProductionOrder::query()->create([
        'company_id' => (int) $fix['company']->id,
        'output_product_id' => (int) $fix['fg']->id,
        'production_bom_id' => $bom->id,
        'rm_warehouse_id' => $rmWarehouseId,
        'fg_warehouse_id' => (int) $fix['fgWarehouse']->id,
        'planned_quantity' => $plannedQuantity,
        'status' => ProductionOrder::STATUS_RELEASED,
        'created_by' => $fix['user']->id,
        'updated_by' => $fix['user']->id,
        'released_at' => now(),
    ]);

    return $order;
}

function formatProductionQuantityForAssertion(float $value): string
{
    return rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.') ?: '0';
}
