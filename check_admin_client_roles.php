<?php

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Checking for Users with BOTH 'admin' and 'client' roles ===\n";

$usersWithBoth = User::whereHas('roles', function($q) {
    $q->where('name', 'admin');
})->whereHas('roles', function($q) {
    $q->where('name', 'client');
})->get();

if ($usersWithBoth->count() > 0) {
    echo "Found " . $usersWithBoth->count() . " users with both roles:\n";
    foreach ($usersWithBoth as $u) {
        echo "- User ID: {$u->id}, Name: {$u->name}, Company ID: {$u->company_id}\n";
        
        // Fix them automatically?
        // Let's just list them first.
    }
} else {
    echo "No users found with both roles.\n";
}

echo "\n=== Checking newly created admins from previous fix ===\n";
// The IDs from previous fix were likely 58 (Company 20), 56 (Company 28 - Yadah Wang), etc.
// Let's check Company 28's admin specifically.
$company28Admin = User::where('company_id', 28)->whereHas('roles', function($q) {
    $q->where('name', 'admin');
})->first();

if ($company28Admin) {
    echo "Company 28 Admin: {$company28Admin->name} (ID: {$company28Admin->id})\n";
    echo "Roles: " . $company28Admin->roles->pluck('name')->implode(', ') . "\n";
}
