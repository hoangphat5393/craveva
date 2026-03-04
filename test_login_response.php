<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

// We need to simulate a request to the login route.
// However, since we are in CLI, we can't easily use the full HTTP stack with middleware without dispatching a request.

echo "Testing Login Route Response...\n";

// Use curl to hit the local server if it's running.
// Since I don't know if the server is running on a specific port (usually 8000), I'll try to guess or use the internal request dispatch.
// Dispatching internally is better as it uses the current code state.

$request = Illuminate\Http\Request::create('/login', 'POST', [
    'email' => 'superadmin@example.com',
    'password' => '12345678',
    '_token' => csrf_token(), // This won't work easily because of session mismatch
]);

// Bypass CSRF for this test if possible, or Mock it.
// Actually, it's easier to just disable VerifyCsrfToken middleware for a moment or use 'RefreshDatabase' trait in tests.
// But I can't modify the code just for a test script easily without affecting the app.

// Alternative: Check what headers jQuery sends.
// It sends: X-Requested-With: XMLHttpRequest
// It sends: Accept: application/json, ...

echo "Simulating Request with wantsJson()...\n";
$request->headers->set('X-Requested-With', 'XMLHttpRequest');
$request->headers->set('Accept', 'application/json');

// We can't easily dispatch through the kernel because of session/cookie handling needed for CSRF.
// So let's just inspect the FortifyServiceProvider logic by instantiating the pipeline or the classes if possible.
// But Fortify uses a pipeline of classes.

// Let's try to hit the URL using curl assuming it is running on localhost (the user is "local").
// But I don't know the port.

// Let's look at the FortifyServiceProvider code I wrote again.
// I will print the relevant parts of FortifyServiceProvider.php to double check my work.

$provider = file_get_contents(__DIR__ . '/app/Providers/FortifyServiceProvider.php');
echo substr($provider, 0, 2000); // Read first 2000 chars

echo "\n\nChecking LoginResponse class definition in the file...\n";
// Simple regex to find the LoginResponse implementation
preg_match('/app->instance\(LoginResponse::class, new class implements LoginResponse \{.*?\n\s*\}\);/s', $provider, $matches);
if ($matches) {
    echo "Found LoginResponse implementation:\n";
    echo $matches[0] . "\n";
} else {
    echo "LoginResponse implementation NOT found via regex (might be formatting).\n";
}
