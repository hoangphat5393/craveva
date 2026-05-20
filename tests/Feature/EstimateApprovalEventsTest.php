<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Estimate;
use App\Models\EstimateApprovalEvent;
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

it('logs submit for review to approval events timeline', function (): void {
    $company = Company::withoutGlobalScopes()->where('status', 'active')->orderBy('id')->first();
    if (! $company instanceof Company) {
        test()->markTestSkipped('No active company.');
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
    }

    $userAuth = UserAuth::query()->find($user->user_auth_id ?? 0);
    if (! $userAuth instanceof UserAuth) {
        test()->markTestSkipped('No UserAuth.');
    }

    $estimate = Estimate::withoutGlobalScopes()
        ->where('company_id', $company->id)
        ->orderByDesc('id')
        ->first();

    if (! $estimate instanceof Estimate) {
        test()->markTestSkipped('No estimate.');
    }

    $permissionId = Permission::query()->where('name', 'edit_estimates')->value('id');
    $typeAllId = DB::table('permission_types')->where('name', 'all')->value('id');
    if ($permissionId === null || $typeAllId === null) {
        test()->markTestSkipped('Missing edit_estimates permission.');
    }

    UserPermission::query()->updateOrCreate(
        ['user_id' => $user->id, 'permission_id' => (int) $permissionId],
        ['permission_type_id' => (int) $typeAllId],
    );
    Cache::forget('permission-edit_estimates-'.$user->id);

    ModuleSetting::withoutGlobalScope(CompanyScope::class)->updateOrCreate(
        [
            'company_id' => $company->id,
            'module_name' => 'estimates_phase1_review',
        ],
        [
            'status' => 'active',
            'type' => 'admin',
            'is_allowed' => 1,
        ],
    );

    $estimate->forceFill([
        'status' => 'draft',
        'president_review_status' => null,
        'vp_pricing_review_status' => null,
    ])->save();

    EstimateApprovalEvent::query()->where('estimate_id', $estimate->id)->delete();

    test()->actingAs($userAuth, 'web')
        ->withSession([
            'company' => $company,
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->post(route('estimates.submit_for_review', $estimate->id), [
            '_token' => csrf_token(),
        ])
        ->assertOk();

    $event = EstimateApprovalEvent::query()
        ->where('estimate_id', $estimate->id)
        ->where('event_type', 'submitted')
        ->first();

    expect($event)->not->toBeNull();

    $estimate->load('approvalEvents.actor');
    $entries = $estimate->approvalTimelineEntries();

    expect($entries)->not->toBeEmpty();
    expect($entries[0]['label'])->toBe(__('modules.estimates.timelineEvent_submitted'));
});
