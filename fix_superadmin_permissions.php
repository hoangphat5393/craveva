<?php

use App\Models\Permission;
use App\Models\PermissionType;
use App\Models\UserPermission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Load Laravel application
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$userId = 150; // Superadmin ID

echo "Fixing permissions for User ID: $userId\n";

// 1. Get all Superadmin Permissions
// We look for permissions belonging to modules where is_superadmin = 1
// Or we can just get ALL permissions if we want to be safe, but let's stick to superadmin ones first as that's what the sidebar checks.
// Actually, the sidebar logic checks SPECIFIC permissions:
$sidebarPermissionsArray = [
    'view_packages',
    'view_companies',
    'manage_billing',
    'view_request',
    'view_admin_faq',
    'view_superadmin',
    'view_superadmin_ticket',
    'manage_superadmin_front_settings',
];

// But user asked for "full permissions", so let's get ALL permissions available in the system
// and assign type 'all' (4) to them.
// Wait, assigning employee permissions to a superadmin might be weird if they don't have the employee role, 
// but usually superadmins should have access to everything.
// However, the `sidebar_superadmin_perms` function explicitly checks `is_superadmin` modules.

$superAdminPermissions = Permission::whereHas('module', function ($query) {
    $query->withoutGlobalScopes()->where('is_superadmin', '1');
})->get();

echo "Found " . $superAdminPermissions->count() . " superadmin-specific permissions.\n";

$allPermissions = Permission::all();
echo "Found " . $allPermissions->count() . " total permissions in system.\n";

// We will assign ALL permissions to be safe, as "Full Permissions" implies everything.
// But primarily we care about the sidebar ones.

$permissionsToAssign = $allPermissions; 

$count = 0;
foreach ($permissionsToAssign as $perm) {
    // Check if exists
    $userPerm = UserPermission::where('user_id', $userId)
        ->where('permission_id', $perm->id)
        ->first();

    if ($userPerm) {
        if ($userPerm->permission_type_id != PermissionType::ALL) {
            $userPerm->permission_type_id = PermissionType::ALL;
            $userPerm->save();
            echo "Updated permission: {$perm->name} to ALL\n";
            $count++;
        }
    } else {
        UserPermission::create([
            'user_id' => $userId,
            'permission_id' => $perm->id,
            'permission_type_id' => PermissionType::ALL,
            'customised_permissions' => 1 // Mark as customised so it doesn't get overwritten by role sync easily
        ]);
        echo "Granted permission: {$perm->name} as ALL\n";
        $count++;
    }
}

echo "Updated/Granted $count permissions.\n";

// Clear Cache
session()->forget('sidebar_superadmin_perms');
cache()->forget('user_perms_' . $userId);
// Also clear file cache if used
\Illuminate\Support\Facades\Artisan::call('cache:clear');
echo "Cache cleared.\n";

echo "Done.\n";
