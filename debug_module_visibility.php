<?php

use App\Models\Company;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\ModuleSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$companyId = 20;
$company = Company::find($companyId);

if (!$company) {
    echo "Company ID $companyId not found.\n";
    exit;
}

echo "=== Debug Module Visibility for Company ID: $companyId ({$company->company_name}) ===\n";

// 1. Check Package
if ($company->package) {
    echo "Package: " . $company->package->name . " (ID: " . $company->package_id . ")\n";
    echo "Package Modules (JSON): " . $company->package->module_in_package . "\n";
} else {
    echo "WARNING: No Package assigned!\n";
}

// 2. Check Module Settings (What is enabled for this company)
$modules = ModuleSetting::where('company_id', $companyId)->where('status', 'active')->get();
echo "\n=== Active Modules in module_settings table ({$modules->count()}) ===\n";
foreach ($modules as $m) {
    echo "- " . $m->module_name . " (" . $m->type . ")\n";
}

// 3. Check Admin Role Permissions
$adminRole = Role::where('company_id', $companyId)->where('name', 'admin')->first();

if (!$adminRole) {
    echo "\nERROR: No 'admin' role found for this company!\n";
} else {
    echo "\n=== Admin Role (ID: {$adminRole->id}) Permissions ===\n";
    
    // Count total permissions assigned
    $permissionCount = DB::table('permission_role')->where('role_id', $adminRole->id)->count();
    echo "Total Permissions Assigned: " . $permissionCount . "\n";
    
    // List some permissions if any
    if ($permissionCount > 0) {
        $samplePermissions = DB::table('permission_role')
            ->join('permissions', 'permission_role.permission_id', '=', 'permissions.id')
            ->where('role_id', $adminRole->id)
            ->limit(5)
            ->pluck('permissions.name');
        echo "Sample permissions: " . $samplePermissions->implode(', ') . "...\n";
    } else {
        echo "WARNING: Admin role has ZERO permissions!\n";
    }
}

// 4. Check User Role Assignment
$user = User::where('company_id', $companyId)->whereHas('roles', function($q) {
    $q->where('name', 'admin');
})->first();

if ($user) {
    echo "\n=== Admin User Found: {$user->name} (ID: {$user->id}) ===\n";
    echo "User has roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
} else {
    echo "\nWARNING: No user found with 'admin' role in this company.\n";
}
