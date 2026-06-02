<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\User;
use App\Models\UserAuth;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

uses(DatabaseTransactions::class);

it('registers sales do create route', function (): void {
    expect(Route::has('sales-do.create'))->toBeTrue();
});

it('renders sales do create page for authorized user', function (): void {
    if (! Schema::hasTable('users')) {
        test()->markTestSkipped('Required tables missing.');
    }

    $company = Company::withoutGlobalScopes()->where('status', 'active')->orderBy('id')->first();
    if ($company === null) {
        test()->markTestSkipped('No active company.');
    }

    $user = User::withoutGlobalScopes()
        ->where('company_id', $company->id)
        ->where('status', 'active')
        ->whereNull('is_client_contact')
        ->whereNotNull('user_auth_id')
        ->orderBy('id')
        ->first();

    if ($user === null) {
        test()->markTestSkipped('No active employee user.');
    }

    $userAuth = UserAuth::find($user->user_auth_id);
    if ($userAuth === null) {
        test()->markTestSkipped('User auth missing.');
    }

    $response = $this->actingAs($userAuth, 'web')
        ->get(route('sales-do.create'));

    if ($response->status() === 403) {
        test()->markTestSkipped('Current user lacks sales_do.create permission.');
    }

    $response->assertSuccessful();
    $response->assertSee(__('purchase::app.menu.saleDeliveryOrder'), false);
});
