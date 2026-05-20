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
function estimateRevisionContext(): ?array
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

    foreach (['edit_estimates', 'approve_estimate_president'] as $permissionName) {
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
        ],
        [
            'status' => 'active',
            'type' => 'admin',
        ],
    );

    Cache::flush();

    return ['userAuth' => $userAuth, 'company' => $company, 'estimate' => $estimate];
}

it('sets revision_required instead of declined when president rejects under phase1', function (): void {
    $context = estimateRevisionContext();
    if ($context === null) {
        return;
    }

    ['userAuth' => $userAuth, 'company' => $company, 'estimate' => $estimate] = $context;

    $estimate->president_review_status = Estimate::INTERNAL_REVIEW_PENDING;
    $estimate->vp_pricing_review_status = Estimate::INTERNAL_REVIEW_PENDING;
    $estimate->status = 'waiting';
    $estimate->save();

    $response = $this->actingAs($userAuth, 'web')
        ->withSession([
            'company' => $company,
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->post(route('estimates.president_review', $estimate->id), [
            '_token' => csrf_token(),
            'decision' => 'rejected',
            'note' => 'Adjust MOQ',
        ]);

    $response->assertOk();
    $response->assertJsonPath('status', 'success');

    $estimate->refresh();

    expect($estimate->status)->toBe(Estimate::STATUS_REVISION_REQUIRED);
    expect($estimate->president_review_status)->toBe(Estimate::INTERNAL_REVIEW_REJECTED);
});

it('allows resubmit after revision_required and clears review state', function (): void {
    $context = estimateRevisionContext();
    if ($context === null) {
        return;
    }

    ['userAuth' => $userAuth, 'company' => $company, 'estimate' => $estimate] = $context;

    $estimate->status = Estimate::STATUS_REVISION_REQUIRED;
    $estimate->president_review_status = Estimate::INTERNAL_REVIEW_REJECTED;
    $estimate->vp_pricing_review_status = Estimate::INTERNAL_REVIEW_PENDING;
    $estimate->save();

    $response = $this->actingAs($userAuth, 'web')
        ->withSession([
            'company' => $company,
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
    expect($estimate->president_reviewed_at)->toBeNull();
});
