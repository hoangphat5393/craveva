<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Currency;
use App\Models\Estimate;
use App\Models\EstimateBomLine;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserAuth;
use App\Models\UserPermission;
use App\Scopes\CompanyScope;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(DatabaseTransactions::class);

/**
 * @return array{userAuth: UserAuth, company: Company, clientId: int, currencyId: int}
 */
function estimateBomLinesTestContext(): array
{
    if (! Schema::hasTable('estimate_bom_lines')) {
        test()->markTestSkipped('estimate_bom_lines table is not migrated.');

        return [];
    }

    $company = Company::withoutGlobalScopes()->where('status', 'active')->orderBy('id')->first();
    if (! $company instanceof Company) {
        test()->markTestSkipped('No active company row found.');

        return [];
    }

    $companyId = (int) $company->id;

    $user = User::withoutGlobalScopes()
        ->where('company_id', $companyId)
        ->where('status', 'active')
        ->whereNull('is_client_contact')
        ->whereNotNull('user_auth_id')
        ->orderBy('id')
        ->first();
    if (! $user instanceof User) {
        test()->markTestSkipped('No active tenant employee found.');

        return [];
    }

    $userAuth = UserAuth::query()->find($user->user_auth_id ?? 0);
    if (! $userAuth instanceof UserAuth) {
        test()->markTestSkipped('No UserAuth found for selected employee.');

        return [];
    }

    if ($userAuth->email_verified_at === null) {
        $userAuth->forceFill(['email_verified_at' => now()])->save();
    }

    foreach (['add_estimates', 'edit_estimates', 'view_estimates'] as $permissionName) {
        $permissionId = Permission::query()->where('name', $permissionName)->value('id');
        $typeAllId = DB::table('permission_types')->where('name', 'all')->value('id');
        if ($permissionId === null || $typeAllId === null) {
            test()->markTestSkipped("Required permission seeds are missing: {$permissionName}.");

            return [];
        }

        UserPermission::query()->updateOrCreate(
            ['user_id' => $user->id, 'permission_id' => (int) $permissionId],
            ['permission_type_id' => (int) $typeAllId],
        );
        Cache::forget('permission-'.$permissionName.'-'.$user->id);
    }

    Cache::forget('user_modules_'.$user->id);

    $clientId = User::withoutGlobalScopes()
        ->where('users.company_id', $companyId)
        ->where('client_details.company_id', $companyId)
        ->join('client_details', 'client_details.user_id', '=', 'users.id')
        ->value('users.id');
    if ($clientId === null) {
        test()->markTestSkipped('No client found for selected company.');

        return [];
    }

    $currencyId = Currency::withoutGlobalScope(CompanyScope::class)
        ->where('company_id', $companyId)
        ->orWhereNull('company_id')
        ->orderBy('id')
        ->value('id');
    if ($currencyId === null) {
        test()->markTestSkipped('No currency found.');

        return [];
    }

    return [
        'userAuth' => $userAuth,
        'company' => $company,
        'clientId' => (int) $clientId,
        'currencyId' => (int) $currencyId,
        'companyId' => $companyId,
    ];
}

it('stores estimate with bom lines', function (): void {
    $ctx = estimateBomLinesTestContext();
    if ($ctx === []) {
        return;
    }

    $response = test()->actingAs($ctx['userAuth'], 'web')
        ->withSession([
            'company' => $ctx['company'],
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->post(route('estimates.store', ['type' => 'draft']), [
            '_token' => csrf_token(),
            'estimate_number' => 'EST-BOM-'.now()->format('YmdHis'),
            'client_id' => $ctx['clientId'],
            'valid_till' => now()->addDays(7)->format($ctx['company']->date_format),
            'sub_total' => 100,
            'total' => 100,
            'currency_id' => $ctx['currencyId'],
            'discount_value' => 0,
            'discount_type' => 'percent',
            'calculate_tax' => 'after_discount',
            'item_name' => ['Commercial SKU'],
            'item_summary' => [''],
            'quantity' => ['1'],
            'cost_per_item' => ['100'],
            'amount' => ['100'],
            'unit_id' => [''],
            'product_id' => [''],
            'taxes' => [[]],
            'bom_material_name' => ['Sugar', 'Creamer'],
            'bom_product_id' => ['', ''],
            'bom_quantity' => ['0.05', '0.02'],
            'bom_unit_cost' => ['1.20', '2.50'],
            'bom_unit_id' => ['', ''],
            'bom_line_id' => ['', ''],
            'bom_notes' => ['', ''],
            'redirect_url' => route('estimates.index'),
        ]);

    $response->assertOk();
    $response->assertJsonPath('status', 'success');

    $estimateId = (int) $response->json('estimateId');
    expect($estimateId)->toBeGreaterThan(0);

    $bomLines = EstimateBomLine::query()
        ->where('estimate_id', $estimateId)
        ->orderBy('sort_order')
        ->get();

    expect($bomLines)->toHaveCount(2);
    expect($bomLines[0]->material_name)->toBe('Sugar');
    expect((float) $bomLines[0]->line_total)->toBe(0.06);
    expect($bomLines[1]->material_name)->toBe('Creamer');
    expect((float) $bomLines[1]->line_total)->toBe(0.05);
});

it('shows estimate detail with bom lines section', function (): void {
    $ctx = estimateBomLinesTestContext();
    if ($ctx === []) {
        return;
    }

    $estimate = Estimate::withoutGlobalScopes()
        ->where('company_id', $ctx['companyId'])
        ->orderByDesc('id')
        ->first();

    if (! $estimate instanceof Estimate) {
        test()->markTestSkipped('No estimate in database.');

        return;
    }

    EstimateBomLine::query()->create([
        'company_id' => $ctx['companyId'],
        'estimate_id' => $estimate->id,
        'material_name' => 'Test Material',
        'quantity' => 1,
        'unit_cost' => 10,
        'line_total' => 10,
        'sort_order' => 1,
    ]);

    $response = test()->actingAs($ctx['userAuth'], 'web')
        ->withSession([
            'company' => $ctx['company'],
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->get(route('estimates.show', $estimate->id));

    $response->assertOk();
    $response->assertSee(__('modules.estimates.bomLinesHeading'), false);
    $response->assertSee('Test Material', false);
});
