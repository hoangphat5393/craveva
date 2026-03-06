<?php

use App\Models\Company;
use App\Models\Module;
use App\Models\ModuleSetting;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $companies = Company::all();

        // 1. Enable Module in ModuleSettings for all companies (admin & employee)
        foreach ($companies as $company) {
            $types = ['admin', 'employee'];
            foreach ($types as $type) {
                $exists = ModuleSetting::withoutGlobalScope(\App\Scopes\CompanyScope::class)
                    ->where('module_name', 'pricing')
                    ->where('type', $type)
                    ->where('company_id', $company->id)
                    ->exists();

                if (! $exists) {
                    ModuleSetting::create([
                        'module_name' => 'pricing',
                        'type' => $type,
                        'company_id' => $company->id,
                        'status' => 'active',
                        'is_allowed' => 1,
                    ]);
                }
            }
        }

        // 2. Create Module Entry
        $module = Module::where('module_name', 'pricing')->first();
        if (! $module) {
            $module = Module::create([
                'module_name' => 'pricing',
                'description' => 'Pricing Module Custom',
            ]);
        }

        // 3. Create Permissions
        $permissions = [
            'view_pricing_tiers' => Permission::ALL_NONE,
            'add_pricing_tiers' => Permission::ALL_NONE,
            'edit_pricing_tiers' => Permission::ALL_NONE,
            'delete_pricing_tiers' => Permission::ALL_NONE,

            'view_client_pricing' => Permission::ALL_NONE,
            'add_client_pricing' => Permission::ALL_NONE,
            'edit_client_pricing' => Permission::ALL_NONE,
            'delete_client_pricing' => Permission::ALL_NONE,

            'view_company_pricing' => Permission::ALL_NONE,
            'add_company_pricing' => Permission::ALL_NONE,
            'edit_company_pricing' => Permission::ALL_NONE,
            'delete_company_pricing' => Permission::ALL_NONE,

            'view_client_tiers' => Permission::ALL_NONE,
            'add_client_tiers' => Permission::ALL_NONE,
            'edit_client_tiers' => Permission::ALL_NONE,
            'delete_client_tiers' => Permission::ALL_NONE,

            'view_volume_discounts' => Permission::ALL_NONE,
            'add_volume_discounts' => Permission::ALL_NONE,
            'edit_volume_discounts' => Permission::ALL_NONE,
            'delete_volume_discounts' => Permission::ALL_NONE,
        ];

        $createdPermissions = [];

        foreach ($permissions as $permissionName => $allowedPerms) {
            $perm = Permission::where('name', $permissionName)->first();
            if (! $perm) {
                $perm = Permission::create([
                    'name' => $permissionName,
                    'display_name' => ucwords(str_replace('_', ' ', $permissionName)),
                    'module_id' => $module->id,
                    'allowed_permissions' => $allowedPerms,
                    'is_custom' => 1,
                ]);
            }
            $createdPermissions[] = $perm;
        }

        // 4. Assign Permissions to Admin Role
        // We need to fetch Admin role for each company? Or is role global?
        // Roles are usually company specific in this system (company_id).

        $roles = Role::where('name', 'admin')->get();

        foreach ($roles as $role) {
            foreach ($createdPermissions as $permission) {
                // Check if already has permission
                $exists = PermissionRole::where('permission_id', $permission->id)
                    ->where('role_id', $role->id)
                    ->exists();

                if (! $exists) {
                    // Default to 'all' (4)
                    PermissionRole::create([
                        'permission_id' => $permission->id,
                        'role_id' => $role->id,
                        'permission_type_id' => 4, // 4 = All
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Optional: Remove permissions
    }
};
