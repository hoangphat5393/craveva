<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CHECKING APP CONFIG ===\n";
$appName = config('app.app_name'); // Note: config key might be 'app.name' usually, but helper uses 'app.app_name'?
// Helper code: return strtolower(config('app.app_name')) === 'craveva';
// Let's check 'app.app_name' and 'app.name'.

$configAppName = config('app.app_name');
$stdAppName = config('app.name');

echo "config('app.app_name'): " . var_export($configAppName, true) . "\n";
echo "config('app.name'): " . var_export($stdAppName, true) . "\n";

$isCraveva = false;
if (function_exists('isCraveva')) {
    $isCraveva = isCraveva();
    echo "isCraveva() returns: " . ($isCraveva ? 'TRUE' : 'FALSE') . "\n";
} else {
    echo "isCraveva() function not found!\n";
    // Check definition in helper file
    // It seems to be in app/Helper/start.php or similar
}

if (!$isCraveva) {
    echo "[WARN] isCraveva() is FALSE. SuperAdmin routes will NOT be loaded.\n";
    echo "Expected 'craveva' (case-insensitive), got: " . strtolower((string)$configAppName) . "\n";
} else {
    echo "[PASS] isCraveva() is TRUE. SuperAdmin routes should be loaded.\n";
}

// Check if route exists
$routes = Illuminate\Support\Facades\Route::getRoutes();
$superAdminRoute = $routes->getByName('superadmin.super_admin_dashboard'); // Guessing name
// Or check by path
$routeExists = false;
foreach ($routes as $route) {
    if ($route->uri() === 'account/super-admin-dashboard') {
        $routeExists = true;
        break;
    }
}

if ($routeExists) {
    echo "[PASS] Route '/account/super-admin-dashboard' exists.\n";
} else {
    echo "[FAIL] Route '/account/super-admin-dashboard' DOES NOT EXIST!\n";
}

echo "=== END ===\n";
