<?php

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$companyId = 28;
$company = Company::find($companyId);

if (!$company) {
    echo "Company $companyId not found.\n";
    exit;
}

echo "=== Company: {$company->company_name} (ID: $companyId) ===\n";

// 1. Check Users directly (assuming users table has company_id)
$users = User::where('company_id', $companyId)->get();
echo "\n[Users Table] Found " . $users->count() . " users with company_id = $companyId:\n";
foreach ($users as $user) {
    echo "- User ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Status: {$user->status}\n";
}

// 2. Check Roles
$roles = Role::where('company_id', $companyId)->get();
echo "\n[Roles Table] Found " . $roles->count() . " roles:\n";
foreach ($roles as $role) {
    $userCount = $role->users()->count();
    echo "- Role ID: {$role->id}, Name: {$role->name}, Display Name: {$role->display_name}, Users Count: $userCount\n";
    
    if ($role->name === 'admin') {
        echo "  -> Checking Admin Users:\n";
        $adminUsers = $role->users()->get();
        if ($adminUsers->isEmpty()) {
            echo "     WARNING: No users found for Admin role!\n";
        } else {
            foreach ($adminUsers as $u) {
                echo "     - Admin User ID: {$u->id}, Name: {$u->name}, Email: {$u->email}, Status: {$u->status}\n";
            }
        }
    }
}

// 3. Check role_user table directly for company roles
$roleIds = $roles->pluck('id')->toArray();
if (!empty($roleIds)) {
    $roleUserEntries = DB::table('role_user')->whereIn('role_id', $roleIds)->get();
    echo "\n[role_user Table] Raw entries for company roles (" . implode(',', $roleIds) . "):\n";
    foreach ($roleUserEntries as $entry) {
        echo "- User ID: {$entry->user_id} <-> Role ID: {$entry->role_id}\n";
        // Check if this user actually exists in users table
        $userExists = User::find($entry->user_id);
        if (!$userExists) {
            echo "  -> CRITICAL: User ID {$entry->user_id} exists in role_user but NOT in users table!\n";
        }
    }
} else {
    echo "\n[role_user Table] No roles found for this company.\n";
}
