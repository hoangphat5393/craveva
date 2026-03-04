<?php

use App\Models\User;
use App\Models\UserAuth;
use App\Models\UserPermission;
use App\Models\Permission;
use App\Scopes\CompanyScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$email = 'superadmin@example.com';
$userAuth = UserAuth::where('email', $email)->first();

if (!$userAuth) {
    echo "Superadmin UserAuth not found!\n";
    exit(1);
}

echo "UserAuth ID: " . $userAuth->id . "\n";

// Login with UserAuth
Auth::login($userAuth);
echo "Logged in as UserAuth ID: " . auth()->id() . "\n";

// Get User Profile
$user = User::withoutGlobalScope(CompanyScope::class)->where('user_auth_id', $userAuth->id)->first();
if (!$user) {
    echo "User profile not found for this Auth ID!\n";
    exit(1);
}
echo "User Profile ID: " . $user->id . "\n";

// Clear session to force reload
session()->forget('sidebar_superadmin_perms');
session()->forget('user'); // Clear cached user in session if any

echo "\n--- Calling sidebar_superadmin_perms() ---\n";
try {
    $sidebarPerms = sidebar_superadmin_perms();
    echo "Sidebar Permissions Count: " . count($sidebarPerms) . "\n";
    print_r($sidebarPerms);
} catch (\Exception $e) {
    echo "Error calling helper: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
