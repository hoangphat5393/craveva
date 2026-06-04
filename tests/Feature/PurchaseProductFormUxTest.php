<?php

declare(strict_types=1);

use App\Enums\ProductType;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Product;
use App\Models\User;
use App\Models\UserAuth;
use App\Models\UserPermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

uses(DatabaseTransactions::class);

it('renders simplified raw material fields on purchase product create modal', function (): void {
    $company = Company::withoutGlobalScopes()->where('status', 'active')->orderBy('id')->first();
    if (! $company instanceof Company) {
        test()->markTestSkipped('No active company.');

        return;
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
        test()->markTestSkipped('No employee user.');

        return;
    }

    $userAuth = UserAuth::query()->find($user->user_auth_id ?? 0);
    if (! $userAuth instanceof UserAuth) {
        test()->markTestSkipped('No UserAuth.');

        return;
    }

    $permissionId = Permission::query()->where('name', 'add_product')->value('id');
    $typeAllId = DB::table('permission_types')->where('name', 'all')->value('id');
    if ($permissionId === null || $typeAllId === null) {
        test()->markTestSkipped('Permission seeds missing.');

        return;
    }

    UserPermission::query()->updateOrCreate(
        ['user_id' => $user->id, 'permission_id' => (int) $permissionId],
        ['permission_type_id' => (int) $typeAllId],
    );
    Cache::forget('permission-add_product-'.$user->id);

    $rawMaterial = Product::withoutGlobalScopes()
        ->where('company_id', $companyId)
        ->where('type', ProductType::RawMaterial->value)
        ->orderBy('id')
        ->first();

    if (! $rawMaterial instanceof Product) {
        test()->markTestSkipped('No raw material product to duplicate on create form.');

        return;
    }

    $response = $this->actingAs($userAuth, 'web')
        ->withSession([
            'company' => $company,
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->get(route('purchase-products.create', [
            'duplicate_product' => $rawMaterial->id,
        ]));

    $response->assertSuccessful();
    $content = $response->getContent();

    expect($content)
        ->toContain('name="purchase_information"')
        ->toContain('value="1"')
        ->toContain('id="purchase_price"')
        ->toContain(__('purchase::app.productFormCostOnlyHelp'))
        ->not->toContain('id="purchase_information"')
        ->toMatch('/product-b2b-extra-pricing-block[^"]*d-none/');
});
