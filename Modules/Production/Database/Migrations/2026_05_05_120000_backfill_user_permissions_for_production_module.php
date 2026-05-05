<?php

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\RoleUser;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * user()->permission() reads user_permissions only, not permission_role.
 * Mirrors {@see Modules\Warehouse\Database\Migrations\2026_03_26_100000_backfill_user_permissions_for_warehouse_module}.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_permissions') || ! Schema::hasTable('permission_role')) {
            return;
        }

        $productionPermissionIds = Permission::query()
            ->whereHas('module', fn ($q) => $q->where('module_name', 'production'))
            ->pluck('id');

        if ($productionPermissionIds->isEmpty()) {
            return;
        }

        $rolePermissionRows = PermissionRole::query()
            ->whereIn('permission_id', $productionPermissionIds)
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
