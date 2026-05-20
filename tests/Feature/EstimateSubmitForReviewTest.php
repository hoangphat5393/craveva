<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Estimate;
use App\Models\ModuleSetting;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserAuth;
use App\Models\UserPermission;
use App\Scopes\CompanyScope;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

uses(DatabaseTransactions::class);

/**
 * @return array{userAuth: UserAuth, company: Company, estimate: Estimate}|null
 */
function estimateSubmitForReviewContext(): ?array
{
    $company = Company::withoutGlobalScopes()->where('status', 'active')->orderBy('id')->first();
    if (! $company instanceof Company) {
        test()->markTestSkipped('No active company.');

        return null;
    }

    $user = User::withoutGlobalScopes()
        ->where('company_id', $company->id)
        ->where('status', 'active')
        ->whereNull('is_client_contact')
        ->whereNotNull('user_auth_id')
        ->orderBy('id')
        ->first();

    if (! $user instanceof User) {
        test()->markTestSkipped('No employee user.');

        return null;
    }

    $userAuth = UserAuth::query()->find($user->user_auth_id ?? 0);
    if (! $userAuth instanceof UserAuth) {
        test()->markTestSkipped('No UserAuth.');

        return null;
    }

    $estimate = Estimate::withoutGlobalScopes()
        ->where('company_id', $company->id)
        ->orderByDesc('id')
        ->first();

    if (! $estimate instanceof Estimate) {
        test()->markTestSkipped('No estimate.');

        return null;
    }

    foreach (['edit_estimates'] as $permissionName) {
        $permissionId = Permission::query()->where('name', $permissionName)->value('id');
        $typeAllId = DB::table('permission_types')->where('name', 'all')->value('id');
        if ($permissionId === null || $typeAllId === null) {
            test()->markTestSkipped("Missing permission: {$permissionName}.");

            return null;
        }

        UserPermission::query()->updateOrCreate(
            ['user_id' => $user->id, 'permission_id' => (int) $permissionId],
            ['permission_type_id' => (int) $typeAllId],
        );
        Cache::forget('permission-'.$permissionName.'-'.$user->id);
    }

    ModuleSetting::withoutGlobalScope(CompanyScope::class)->updateOrCreate(
        [
            'company_id' => $company->id,
            'module_name' => 'estimates_phase1_review',
            'type' => 'admin',
        ],
        [
            'is_allowed' => 1,
            'status' => 'active',
        ],
    );

    return [
        'userAuth' => $userAuth,
        'company' => $company,
        'estimate' => $estimate,
    ];
}

it('submits estimate for internal review when phase1 is enabled', function (): void {
    $ctx = estimateSubmitForReviewContext();
    if ($ctx === null) {
        return;
    }

    $estimate = $ctx['estimate'];
    $estimate->forceFill([
        'status' => 'draft',
        'president_review_status' => null,
        'vp_pricing_review_status' => null,
        'president_reviewed_by' => null,
        'vp_pricing_reviewed_by' => null,
    ])->save();

    $response = test()->actingAs($ctx['userAuth'], 'web')
        ->withSession([
            'company' => $ctx['company'],
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->post(route('estimates.submit_for_review', $estimate->id), [
            '_token' => csrf_token(),
        ]);

    $response->assertOk();
    $response->assertJsonPath('status', 'success');

    $estimate->refresh();
    expect($estimate->status)->toBe('waiting');
    expect($estimate->president_review_status)->toBe(Estimate::INTERNAL_REVIEW_PENDING);
    expect($estimate->vp_pricing_review_status)->toBe(Estimate::INTERNAL_REVIEW_PENDING);
});

it('forbids submit for review when phase1 module is disabled', function (): void {
    $ctx = estimateSubmitForReviewContext();
    if ($ctx === null) {
        return;
    }

    ModuleSetting::withoutGlobalScope(CompanyScope::class)->updateOrCreate(
        [
            'company_id' => $ctx['company']->id,
            'module_name' => 'estimates_phase1_review',
            'type' => 'admin',
        ],
        [
            'is_allowed' => 0,
            'status' => 'deactive',
        ],
    );

    Cache::flush();

    $response = test()->actingAs($ctx['userAuth'], 'web')
        ->withSession([
            'company' => $ctx['company'],
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->post(route('estimates.submit_for_review', $ctx['estimate']->id), [
            '_token' => csrf_token(),
        ]);

    $response->assertForbidden();
});
