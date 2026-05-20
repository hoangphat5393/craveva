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
        $module = Module::where('module_name', 'estimates')->first();
        if (! $module) {
            return;
        }

        $permissions = [
            [
                'name' => 'approve_estimate_president',
                'display_name' => 'Approve quotation (President)',
                'allowed_permissions' => Permission::ALL_NONE,
                'is_custom' => 1,
            ],
            [
                'name' => 'approve_estimate_vp_pricing',
                'display_name' => 'Approve quotation (VP pricing)',
                'allowed_permissions' => Permission::ALL_NONE,
                'is_custom' => 1,
            ],
        ];

        foreach ($permissions as $perm) {
            $permission = Permission::firstOrCreate(
                ['name' => $perm['name']],
                [
                    'display_name' => $perm['display_name'],
                    'is_custom' => $perm['is_custom'],
                    'module_id' => $module->id,
                    'allowed_permissions' => $perm['allowed_permissions'],
                ]
            );

            $companies = Company::select('id')->get();
            foreach ($companies as $company) {
                $role = Role::where('name', 'admin')
                    ->where('company_id', $company->id)
                    ->first();
                if (! $role) {
                    continue;
                }
                $permissionRole = PermissionRole::where('permission_id', $permission->id)->where('role_id', $role->id)->first() ?: new PermissionRole;
                $permissionRole->permission_id = $permission->id;
                $permissionRole->role_id = $role->id;
                $permissionRole->permission_type_id = 4;
                $permissionRole->save();
            }

            $adminUsers = User::allAdmins();
            foreach ($adminUsers as $adminUser) {
                $userPermission = UserPermission::where('permission_id', $permission->id)->where('user_id', $adminUser->id)->first() ?: new UserPermission;
                $userPermission->user_id = $adminUser->id;
                $userPermission->permission_id = $permission->id;
                $userPermission->permission_type_id = 4;
                $userPermission->save();
            }
        }
    }

    public function down(): void
    {
        Permission::whereIn('name', [
            'approve_estimate_president',
            'approve_estimate_vp_pricing',
        ])->delete();
    }
};
