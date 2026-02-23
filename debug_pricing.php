<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting debug...\n";

// Login using ID directly
$userId = 1;
$user = \App\Models\User::withoutGlobalScopes()->find($userId);

if (!$user) {
    echo "User 1 not found in DB!\n";
    exit;
}

echo "User found: " . $user->name . " (ID: $user->id)\n";

try {
    auth()->loginUsingId($userId);
    echo "Logged in successfully.\n";
} catch (\Exception $e) {
    echo "Login failed: " . $e->getMessage() . "\n";
    // Continue anyway to check other things
}

// Force set user for the helper if login failed
if (!auth()->check()) {
    echo "Auth check failed, mocking auth...\n";
    auth()->setUser($user);
}

echo "Current User ID: " . auth()->id() . "\n";

// Check user roles
echo "Roles: " . implode(', ', user_roles()) . "\n";

echo "--------------------------------------------------\n";
echo "Checking 'user_modules' helper output:\n";
// Clear cache first to be sure
cache()->forget('user_modules_' . $userId);
$modules = user_modules();
print_r($modules);

echo "--------------------------------------------------\n";
echo "Checking ModuleSetting directly:\n";

$settings = \App\Models\ModuleSetting::withoutGlobalScopes()
    ->where('module_name', 'pricing')
    ->get();

if ($settings->isEmpty()) {
    echo "No settings found for 'pricing' module!\n";
} else {
    foreach ($settings as $s) {
        echo "ID: {$s->id}, Company: {$s->company_id}, Type: {$s->type}, Status: {$s->status}, Is Allowed: {$s->is_allowed}\n";
    }
}

echo "--------------------------------------------------\n";
echo "Checking Permissions for User:\n";
try {
    // We need to verify if the user has the permission directly or via role
    // user_modules() logic:
    /*
        $module = \App\Models\ModuleSetting::where('is_allowed', 1);
        if (in_array('admin', user_roles())) {
            $module = $module->where('type', 'admin');
        } ...
    */
    
    echo "User Roles for query: " . print_r(user_roles(), true) . "\n";
    
    // Simulate the query in user_modules()
    $moduleQuery = \App\Models\ModuleSetting::withoutGlobalScopes()
        ->where('is_allowed', 1)
        ->where('module_name', 'pricing');
        
    if (in_array('admin', user_roles())) {
        echo "Filtering by type 'admin'\n";
        $moduleQuery = $moduleQuery->where('type', 'admin');
    }
    
    $result = $moduleQuery->first();
    if ($result) {
        echo "Query found pricing module: YES\n";
        echo "Status: " . $result->status . "\n";
    } else {
        echo "Query found pricing module: NO\n";
    }

} catch (\Exception $e) {
    echo "Permission check error: " . $e->getMessage() . "\n";
}

echo "--------------------------------------------------\n";
echo "Done.\n";
