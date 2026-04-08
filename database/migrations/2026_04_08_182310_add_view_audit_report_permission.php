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
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $module = Module::where('module_name', 'reports')->first();

        if (is_null($module)) {
            return;
        }

        $permissionName = 'view_audit_report';

        $permission = Permission::firstOrCreate(
            ['name' => $permissionName],
            [
                'display_name' => 'View Audit Report',
                'is_custom' => 1,
                'module_id' => $module->id,
                'allowed_permissions' => Permission::ALL_NONE,
            ]
        );

        $companies = Company::query()->select('id')->get();

        foreach ($companies as $company) {
            $role = Role::where('name', 'admin')
                ->where('company_id', $company->id)
                ->first();

            if (is_null($role)) {
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permission = Permission::where('name', 'view_audit_report')->first();

        if (is_null($permission)) {
            return;
        }

        PermissionRole::where('permission_id', $permission->id)->delete();
        UserPermission::where('permission_id', $permission->id)->delete();
        $permission->delete();
    }
};
