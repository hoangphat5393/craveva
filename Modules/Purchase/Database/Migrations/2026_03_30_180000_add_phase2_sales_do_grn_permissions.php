<?php

use App\Models\Company;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $module = Module::where('module_name', 'purchase')->first();
        if (! $module) {
            return;
        }

        $permissionNames = [
            'view_sales_do',
            'create_sales_do',
            'update_sales_do',
            'ship_sales_do',
            'cancel_sales_do',
            'view_grn',
            'create_grn',
            'update_grn',
            'change_status_grn',
            'delete_grn',
        ];

        $companies = Company::all();

        foreach ($permissionNames as $permissionName) {
            $permission = Permission::firstOrCreate([
                'name' => $permissionName,
                'display_name' => ucwords(str_replace('_', ' ', $permissionName)),
                'is_custom' => 1,
                'module_id' => $module->id,
                'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
            ]);

            foreach ($companies as $company) {
                $role = Role::query()
                    ->where('name', 'admin')
                    ->where('company_id', $company->id)
                    ->first();

                if ($role) {
                    PermissionRole::firstOrCreate([
                        'permission_id' => $permission->id,
                        'role_id' => $role->id,
                        'permission_type_id' => 4,
                    ]);
                }

                $admins = User::allAdmins($company->id);
                foreach ($admins as $admin) {
                    UserPermission::firstOrCreate([
                        'user_id' => $admin->id,
                        'permission_id' => $permission->id,
                        'permission_type_id' => 4,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Permission::query()
            ->whereIn('name', [
                'view_sales_do',
                'create_sales_do',
                'update_sales_do',
                'ship_sales_do',
                'cancel_sales_do',
                'view_grn',
                'create_grn',
                'update_grn',
                'change_status_grn',
                'delete_grn',
            ])
            ->delete();
    }
};
