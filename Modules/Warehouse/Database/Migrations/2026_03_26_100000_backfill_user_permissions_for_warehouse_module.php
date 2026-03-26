<?php

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\RoleUser;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * user()->permission() reads user_permissions only, not permission_role.
 * The warehouse setup migration attached permissions to roles but did not
 * populate user_permissions for existing users — company admins saw the menu
 * (via Inventory fallback) but add/view_warehouses returned false.
 *
 * This migration copies each warehouse permission_role row onto every user
 * assigned that role (same pattern as add_view_project_orders migration).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_permissions') || ! Schema::hasTable('permission_role')) {
            return;
        }

        $warehousePermissionIds = Permission::query()
            ->whereHas('module', fn ($q) => $q->where('module_name', 'warehouse'))
            ->pluck('id');

        if ($warehousePermissionIds->isEmpty()) {
            return;
        }

        $rolePermissionRows = PermissionRole::query()
            ->whereIn('permission_id', $warehousePermissionIds)
            ->get(['role_id', 'permission_id', 'permission_type_id']);

        foreach ($rolePermissionRows as $pr) {
            $userIds = RoleUser::query()
                ->where('role_id', $pr->role_id)
                ->pluck('user_id');

            foreach ($userIds as $userId) {
                UserPermission::query()->updateOrCreate(
                    [
                        'user_id' => $userId,
                        'permission_id' => $pr->permission_id,
                    ],
                    [
                        'permission_type_id' => $pr->permission_type_id,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        // Intentionally empty: do not strip user_permissions on rollback.
    }
};
