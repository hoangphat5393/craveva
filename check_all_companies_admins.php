<?php

use App\Models\Company;
use App\Models\Role;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking all companies for active admins...\n";

$companies = Company::all();
$missingAdmins = [];

foreach ($companies as $company) {
    // Logic from Company::firstActiveAdmin
    $adminRole = Role::where('name', 'admin')->where('company_id', $company->id)->first();
    
    if (!$adminRole) {
        echo "Company ID {$company->id} ({$company->company_name}): ERROR - No 'admin' role found.\n";
        $missingAdmins[] = $company->id;
        continue;
    }
    
    $adminUser = $adminRole->users()->first();
    
    if (!$adminUser) {
        echo "Company ID {$company->id} ({$company->company_name}): WARNING - 'admin' role exists but NO USERS assigned.\n";
        $missingAdmins[] = $company->id;
    } else {
        // echo "Company ID {$company->id}: OK (Admin: {$adminUser->name})\n";
    }
}

if (empty($missingAdmins)) {
    echo "All companies have at least one admin user.\n";
} else {
    echo "Found " . count($missingAdmins) . " companies with missing admins.\n";
}
