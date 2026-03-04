<?php

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Fixing Role Conflicts for Admin Users ===\n";

// Find all users who have 'admin' role
$adminUsers = User::whereHas('roles', function($q) {
    $q->where('name', 'admin');
})->get();

foreach ($adminUsers as $user) {
    // Check if they also have 'client' role
    if ($user->hasRole('client')) {
        echo "User {$user->name} (ID: {$user->id}, Company: {$user->company_id}) has 'client' role. Removing...\n";
        
        $clientRole = Role::where('company_id', $user->company_id)->where('name', 'client')->first();
        if ($clientRole) {
            $user->roles()->detach($clientRole->id);
            echo " -> 'client' role removed.\n";
        }
    }
}

echo "\n=== Done. ===\n";
