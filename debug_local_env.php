<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Database Default: " . config('database.default') . "\n";
$default = config('database.default');
$config = config("database.connections.$default");
echo "Driver: " . ($config['driver'] ?? 'N/A') . "\n";
echo "Host: " . ($config['host'] ?? 'N/A') . "\n";
echo "Database: " . ($config['database'] ?? 'N/A') . "\n";
// echo "Username: " . ($config['username'] ?? 'N/A') . "\n"; // Sensitive

\Illuminate\Support\Facades\Log::info('Test Log from debug_local_env.php');
echo "\nLog path: " . storage_path('logs/laravel.log') . "\n";
echo "Session path: " . config('session.files') . "\n";
if (is_writable(storage_path('logs'))) {
    echo "Logs dir writable: YES\n";
} else {
    echo "Logs dir writable: NO\n";
}

echo "\n--- UserAuth Scope Check ---\n";
$userAuth = \App\Models\UserAuth::where('email', 'superadmin@example.com')->first();
if ($userAuth) {
    echo "UserAuth found via direct query: ID {$userAuth->id}\n";
    echo "Password Hash: {$userAuth->password}\n";
} else {
    echo "UserAuth NOT found via direct query.\n";
}

echo "\n--- Attempting Login Simulation ---\n";
// Simulate the logic from FortifyServiceProvider
$email = 'superadmin@example.com';
$password = '12345678';

$user = \App\Models\UserAuth::where('email', $email)->first();

if ($user && \Illuminate\Support\Facades\Hash::check($password, $user->password)) {
    echo "Hash check PASSED.\n";
    
    try {
        \App\Models\UserAuth::validateLoginActiveDisabled($user);
        echo "validateLoginActiveDisabled PASSED.\n";

        echo "Checking User Relationship...\n";
        $appUser = $user->userWithoutCompany ?? $user->user;
        if ($appUser) {
            echo "Linked User found: ID {$appUser->id}, Email: {$appUser->email}\n";
            echo "Is Superadmin: " . ($appUser->is_superadmin ? 'YES' : 'NO') . "\n";
            echo "Status in DB: " . $appUser->status . "\n";
            
            $cacheKey = 'user_is_active_' . $appUser->id;
            $cachedStatus = \Illuminate\Support\Facades\Cache::get($cacheKey);
            echo "Current Cache Key '$cacheKey': " . ($cachedStatus === null ? 'NULL' : ($cachedStatus ? 'TRUE' : 'FALSE')) . "\n";

            if ($cachedStatus === false) {
                echo "FIXING: Cache is FALSE but user is active. Clearing cache key...\n";
                \Illuminate\Support\Facades\Cache::forget($cacheKey);
                $newCachedStatus = \Illuminate\Support\Facades\Cache::get($cacheKey);
                echo "After Clear - Cache Key '$cacheKey': " . ($newCachedStatus === null ? 'NULL' : ($newCachedStatus ? 'TRUE' : 'FALSE')) . "\n";
                
                // Re-simulate the middleware logic
                $isActive = \Illuminate\Support\Facades\Cache::rememberForever($cacheKey, function () use ($appUser) {
                    return \App\Models\User::where('id', $appUser->id)
                        ->where('status', 'active')
                        ->exists();
                });
                echo "Re-calculated Cache Value: " . ($isActive ? 'TRUE' : 'FALSE') . "\n";
            }
        } else {
            echo "Linked User NOT FOUND!\n";
        }

    } catch (\Exception $e) {
        echo "validateLoginActiveDisabled FAILED: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "Hash check FAILED or User not found.\n";
    if ($user) {
        echo "User found, hash mismatch.\n";
    }
}
