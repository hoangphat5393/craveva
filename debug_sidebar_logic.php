<?php

use App\Models\User;
use App\Models\UserAuth;
use App\Models\Permission;
use App\Models\ModuleSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Scopes\CompanyScope;

require __DIR__ . '/bootstrap/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- DEBUG SIDEBAR LOGIC START ---\n";

// 1. Login as Super Admin
$email = 'superadmin@example.com';
$userAuth = UserAuth::where('email', $email)->first();

if (!$userAuth) {
    echo "UserAuth not found for $email\n";
    exit;
}

echo "UserAuth found: ID {$userAuth->id}\n";
Auth::login($userAuth);
echo "Auth::id(): " . auth()->id() . "\n";

// Clear session to force regeneration
session()->forget('user');
session()->forget('company');
session()->forget('sidebar_superadmin_perms');

// 2. Test user() helper
$user = user();
echo "user() type: " . get_class($user) . "\n";
echo "user()->id: " . $user->id . "\n";
echo "user()->is_superadmin: " . ($user->is_superadmin ? 'TRUE' : 'FALSE') . "\n";
echo "user()->company_id: " . ($user->company_id ?? 'NULL') . "\n";

// 3. Check Modules
echo "\n--- CHECKING MODULES ---\n";
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

$permissions = Permission::whereIn('name', $sidebarPermissionsArray)->get();
echo "Found " . $permissions->count() . " permissions in DB out of " . count($sidebarPermissionsArray) . " requested.\n";

foreach ($permissions as $perm) {
    echo "Permission: {$perm->name} (ID: {$perm->id})\n";
    if ($perm->module) {
        echo "  - Linked Module: {$perm->module->module_name} (ID: {$perm->module_id})\n";
        echo "  - Module is_superadmin: " . $perm->module->is_superadmin . "\n";
        // Check Global Scope on Module if any
        // The query in helper uses: withoutGlobalScopes()->where('is_superadmin', '1')
    } else {
        echo "  - NO MODULE LINKED\n";
    }
}

// 4. Test sidebar_superadmin_perms()
echo "\n--- TESTING SIDEBAR_SUPERADMIN_PERMS ---\n";
$perms = sidebar_superadmin_perms();
echo "Result count: " . count($perms) . "\n";
print_r($perms);

echo "\n--- DEBUG SIDEBAR LOGIC END ---\n";
