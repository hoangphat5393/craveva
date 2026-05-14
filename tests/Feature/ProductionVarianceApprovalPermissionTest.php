<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\UserPermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Modules\Production\Entities\ProductionBatch;
use Modules\Production\Entities\ProductionBatchOutput;
use Modules\Production\Entities\ProductionOrder;

uses(DatabaseTransactions::class);

/**
 * @return array{order: ProductionOrder, batch: ProductionBatch, output: ProductionBatchOutput}
 */
function createDraftProductionOrderWithBatchOutputForVariancePermissionTest(array $fix): array
{
    $companyId = (int) $fix['company']->id;

    $order = ProductionOrder::query()->create([
        'company_id' => $companyId,
        'status' => ProductionOrder::STATUS_DRAFT,
        'output_product_id' => $fix['fg']->id,
        'production_bom_id' => null,
        'rm_warehouse_id' => $fix['rmWarehouse']->id,
        'fg_warehouse_id' => $fix['fgWarehouse']->id,
        'planned_quantity' => 100,
    ]);

    $batch = ProductionBatch::query()->create([
        'company_id' => $companyId,
        'production_order_id' => $order->id,
        'batch_code' => 'VAR-PERM-'.uniqid(),
    ]);

    $output = ProductionBatchOutput::query()->create([
        'company_id' => $companyId,
        'production_batch_id' => $batch->id,
        'output_product_id' => $fix['fg']->id,
        'quantity' => 1,
        'batch_number' => 'OUT-PERM',
        'warehouse_id' => $fix['fgWarehouse']->id,
    ]);

    return compact('order', 'batch', 'output');
}

it('forbids approve FG variance without edit_production_orders', function (): void {
    $fix = productionTenantFlowFixtures();
    if ($fix === null) {
        return;
    }

    ['batch' => $batch, 'output' => $output] = createDraftProductionOrderWithBatchOutputForVariancePermissionTest($fix);

    $editPermissionId = (int) Permission::query()->where('name', 'edit_production_orders')->value('id');
    expect($editPermissionId)->toBeGreaterThan(0);

    UserPermission::query()
        ->where('user_id', $fix['user']->id)
        ->where('permission_id', $editPermissionId)
        ->delete();

    Cache::forget('permission-edit_production_orders-'.$fix['user']->id);

    $session = [
        'company' => $fix['company'],
        'multi_company_selected' => 1,
        'user_company_count' => 1,
    ];

    $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->get(route('production.batches.show', $batch))
        ->assertSuccessful();

    $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->post(route('production.outputs.approve-variance', $output), [
            '_token' => csrf_token(),
        ])
        ->assertForbidden();
});

it('allows approve FG variance when user retains edit_production_orders', function (): void {
    $fix = productionTenantFlowFixtures();
    if ($fix === null) {
        return;
    }

    ['batch' => $batch, 'output' => $output] = createDraftProductionOrderWithBatchOutputForVariancePermissionTest($fix);

    $session = [
        'company' => $fix['company'],
        'multi_company_selected' => 1,
        'user_company_count' => 1,
    ];

    $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->get(route('production.batches.show', $batch))
        ->assertSuccessful();

    $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->post(route('production.outputs.approve-variance', $output), [
            '_token' => csrf_token(),
        ])
        ->assertRedirect();

    $output->refresh();
    expect($output->approved_by)->toBe((int) $fix['user']->id);
    expect($output->approved_at)->not->toBeNull();
});
