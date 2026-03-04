<?php

use App\Models\User;
use App\Models\Company;
use App\Models\UserAuth;
use App\Scopes\CompanyScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n--- START DEBUGGING LOCAL LOGIN ---\n";

// 1. Get Superadmin Credentials
$email = 'superadmin@example.com';
$userAuth = UserAuth::where('email', $email)->first();

if (!$userAuth) {
    die("ERROR: UserAuth not found for email: $email\n");
}
echo "1. UserAuth Found: ID {$userAuth->id}, Email {$userAuth->email}\n";

// 2. Simulate Login
Auth::loginUsingId($userAuth->id);
if (auth()->check()) {
    echo "2. Auth::login() successful. Auth ID: " . auth()->id() . "\n";
} else {
    die("ERROR: Auth::login() failed.\n");
}

// 3. Check Helper user() Logic (BEFORE Session Company Set)
echo "3. Testing user() helper BEFORE company session set...\n";
$userHelper = user();
if ($userHelper) {
    echo "   -> user() returned User ID: {$userHelper->id}\n";
} else {
    echo "   -> user() returned NULL (This would cause logout)\n";
}

// 4. Simulate Company Session (The problematic step)
$company = Company::where('status', 'active')->first();
if ($company) {
    Session::put('company', $company);
    echo "4. Session 'company' set to ID: {$company->id}\n";
} else {
    echo "4. No active company found to test session scope.\n";
}

// 5. Test User Retrieval WITH Global Scope
echo "5. Testing User::find() WITH Global Scope...\n";
// This mimics what happens inside user() helper or LoginController
$userWithScope = User::where('user_auth_id', $userAuth->id)->first();
if ($userWithScope) {
    echo "   -> Found User: ID {$userWithScope->id} (Scope didn't block)\n";
} else {
    echo "   -> User NOT FOUND (Blocked by CompanyScope)\n";
}

// 6. Test User Retrieval WITHOUT Global Scope
echo "6. Testing User::withoutGlobalScope() ...\n";
$userWithoutScope = User::withoutGlobalScope(CompanyScope::class)
    ->where('user_auth_id', $userAuth->id)
    ->first();

if ($userWithoutScope) {
    echo "   -> Found User: ID {$userWithoutScope->id}\n";
} else {
    echo "   -> User STILL NOT FOUND even without scope (Data issue?)\n";
}

// 7. Check Helper user() Logic (AFTER Session Company Set)
echo "7. Testing user() helper AFTER company session set...\n";
// We need to clear static cache or session user if any to test fresh retrieval
Session::forget('user');
$userHelperAfter = user();
if ($userHelperAfter) {
    echo "   -> user() returned User ID: {$userHelperAfter->id}\n";
} else {
    echo "   -> user() returned NULL (CRITICAL: This causes the logout/redirect loop)\n";

    // Debug why it returned null inside the helper logic
    echo "      DEBUGGING HELPER LOGIC:\n";
    $authId = auth()->id();
    echo "      - Auth ID: $authId\n";
    echo "      - Session Company: " . (session()->has('company') ? 'Yes' : 'No') . "\n";

    $queryLog = DB::getQueryLog();
    // Enable query log for next query
    DB::enableQueryLog();
    $retryUser = User::where('user_auth_id', $authId)->where('status', 'active')->first();
    echo "      - Standard Query Result: " . ($retryUser ? 'Found' : 'Not Found') . "\n";
    // print_r(DB::getQueryLog());
}

echo "--- END DEBUGGING ---\n";
