<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Company;
use App\Models\Role;

$companyId = 28;
$company = Company::find($companyId);

if (!$company) {
    echo "Company $companyId not found.\n";
    exit;
}

echo "Company: " . $company->company_name . "\n";

$role = Role::where('name', 'admin')->where('company_id', $companyId)->first();

if ($role) {
    echo "Role 'admin' found. ID: " . $role->id . "\n";
    $userCount = $role->users()->count();
    echo "Users with this role: " . $userCount . "\n";
    
    if ($userCount > 0) {
        echo "First user ID: " . $role->users()->first()->id . "\n";
    }
} else {
    echo "Role 'admin' NOT found for company $companyId.\n";
}

// Check if there are ANY users for this company
$anyUsers = \App\Models\User::withoutGlobalScope(\App\Scopes\ActiveScope::class)->where('company_id', $companyId)->count();
echo "Total users in company: $anyUsers\n";

// Check permissions/roles for any user
$firstUser = \App\Models\User::withoutGlobalScope(\App\Scopes\ActiveScope::class)->where('company_id', $companyId)->first();
if ($firstUser) {
    echo "First user ID: " . $firstUser->id . "\n";
    // Check their roles
    // Assuming belongsToMany 'roles' relation exists on User
    try {
        echo "User roles: " . $firstUser->roles->pluck('name')->implode(', ') . "\n";
    } catch (\Exception $e) {
        echo "Could not fetch user roles: " . $e->getMessage() . "\n";
    }
}
