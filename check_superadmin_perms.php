<?php

use App\Models\User;
use App\Models\UserAuth;
use App\Models\Permission;
use App\Models\UserPermission;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 1. Get Super Admin User (from users table)
$user = User::where('email', 'superadmin@example.com')->first();
if (!$user) {
    echo "User superadmin@example.com not found. Trying ID 1.\n";
    $user = User::find(1);
}

if (!$user) {
    die("Super Admin user not found.\n");
}

echo "Checking permissions for User ID: {$user->id}, Email: {$user->email}, Is SuperAdmin: {$user->is_superadmin}\n";

// 2. Login as UserAuth to enable user() helper
if ($user->user_auth_id) {
    $userAuth = UserAuth::find($user->user_auth_id);
    if ($userAuth) {
        auth()->login($userAuth);
        echo "Logged in as UserAuth ID: {$userAuth->id}\n";
    } else {
        echo "UserAuth not found for ID: {$user->user_auth_id}\n";
    }
} else {
    echo "User has no user_auth_id\n";
}

// 3. Check sidebar_superadmin_perms() output
echo "\n--- sidebar_superadmin_perms() Output ---\n";
try {
    // Clear session to force recalculation
    session()->forget('sidebar_superadmin_perms');
    $perms = sidebar_superadmin_perms();
    print_r($perms);
} catch (\Exception $e) {
    echo "Error calling sidebar_superadmin_perms: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

// 4. Check Database Permissions
echo "\n--- Database Permissions (Super Admin Modules) ---\n";
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

$superadminPermissions = Permission::whereIn('name', $sidebarPermissionsArray)
    ->with('module')
    ->get();

if ($superadminPermissions->isEmpty()) {
    echo "WARNING: No permissions found in DB for the requested names!\n";
}

foreach ($superadminPermissions as $perm) {
    $moduleName = $perm->module ? $perm->module->module_name : 'NULL MODULE';
    $isSuperAdminModule = $perm->module ? $perm->module->is_superadmin : 'N/A';
    echo "Permission: {$perm->name} (ID: {$perm->id}) - Module: {$moduleName} (Is SuperAdmin: {$isSuperAdminModule})\n";
    
    // Check User Permission
    $userPerm = UserPermission::where('user_id', $user->id)
        ->where('permission_id', $perm->id)
        ->first();
        
    if ($userPerm) {
        echo "  -> User Permission: Type ID {$userPerm->permission_type_id}\n";
    } else {
        echo "  -> User Permission: NOT SET (Will default to 5)\n";
    }
}
