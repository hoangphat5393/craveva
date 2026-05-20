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
use Illuminate\Support\Facades\Schema;

uses(DatabaseTransactions::class);

it('persists raw material product type after column supports product types', function (): void {
    if (! Schema::hasTable('products')) {
        test()->markTestSkipped('products table missing.');

        return;
    }

    $column = DB::select("SHOW COLUMNS FROM products WHERE Field = 'type'");
    $columnType = $column[0]->Type ?? '';
    if (is_string($columnType) && str_contains(strtolower($columnType), 'enum(') && ! str_contains($columnType, 'raw_material')) {
        test()->markTestSkipped('products.type is still legacy enum(goods,service); run migration expand_products_type_column_for_product_types.');
    }

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

    $permissionId = Permission::query()->where('name', 'edit_product')->value('id');
    $typeAllId = DB::table('permission_types')->where('name', 'all')->value('id');
    if ($permissionId === null || $typeAllId === null) {
        test()->markTestSkipped('Permission seeds missing.');

        return;
    }

    UserPermission::query()->updateOrCreate(
        ['user_id' => $user->id, 'permission_id' => (int) $permissionId],
        ['permission_type_id' => (int) $typeAllId],
    );
    Cache::forget('permission-edit_product-'.$user->id);

    $product = Product::withoutGlobalScopes()
        ->where('company_id', $companyId)
        ->orderBy('id')
        ->first();
    if (! $product instanceof Product) {
        test()->markTestSkipped('No product to update.');

        return;
    }

    $product->type = ProductType::RawMaterial->value;
    $product->save();

    expect(
        Product::withoutGlobalScopes()->where('id', $product->id)->value('type')
    )->toBe(ProductType::RawMaterial->value);
});
