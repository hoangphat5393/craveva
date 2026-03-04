<?php

use App\Models\User;
use App\Models\UserAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "--- Database Diagnosis ---\n";
echo "Default Connection: " . config('database.default') . "\n";
try {
    echo "Database Name: " . DB::connection()->getDatabaseName() . "\n";
} catch (\Exception $e) {
    echo "Error connecting to database: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n--- Table Checks ---\n";
$tables = DB::select('SHOW TABLES');
$tableNames = array_map('current', json_decode(json_encode($tables), true));
echo "Tables found: " . count($tableNames) . "\n";
echo "users table exists: " . (in_array('users', $tableNames) ? 'YES' : 'NO') . "\n";
echo "user_auths table exists: " . (in_array('user_auths', $tableNames) ? 'YES' : 'NO') . "\n";

echo "\n--- User Checks ---\n";
$email = 'superadmin@example.com';
$password = '12345678';

$user = User::where('email', $email)->first();
if ($user) {
    echo "User found in 'users' table (ID: {$user->id}).\n";
    echo "is_superadmin: " . $user->is_superadmin . "\n";
    echo "user_auth_id: " . $user->user_auth_id . "\n";
} else {
    echo "User NOT found in 'users' table.\n";
}

$userAuth = UserAuth::where('email', $email)->first();
if ($userAuth) {
    echo "User found in 'user_auths' table (ID: {$userAuth->id}).\n";
    if (Hash::check($password, $userAuth->password)) {
        echo "Password matches in 'user_auths'.\n";
    } else {
        echo "Password DOES NOT match in 'user_auths'.\n";
        // Update it
        $userAuth->password = Hash::make($password);
        $userAuth->save();
        echo "Password updated to match.\n";
    }
} else {
    echo "User NOT found in 'user_auths' table.\n";
}

echo "\n--- Auth Attempt Simulation ---\n";
// Simulate what Fortify does
if ($userAuth && Hash::check($password, $userAuth->password)) {
    echo "Credentials check passed (Manual Hash Check).\n";
} else {
    echo "Credentials check FAILED (Manual Hash Check).\n";
}

// Check Fortify logic
// Fortify uses a closure in FortifyServiceProvider.php
// We can't easily invoke the closure directly, but we can replicate its logic
// logic: UserAuth::where('email', $request->email)->first() && Hash::check...

echo "\n--- End Diagnosis ---\n";
