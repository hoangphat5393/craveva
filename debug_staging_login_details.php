<?php
// debug_staging_login_details.php
require '/var/www/craveva-staging/current/craveva/vendor/autoload.php';
$app = require_once '/var/www/craveva-staging/current/craveva/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\AttendanceSetting;
use App\Models\GlobalSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\UserAuth;
use Illuminate\Validation\ValidationException;

$email = 'hoangphat5393@gmail.com';

echo "--- User Details ---\n";
$user = User::withoutGlobalScope(App\Scopes\ActiveScope::class)->where('email', $email)->first();

if (!$user) {
    echo "User NOT FOUND in 'users' table.\n";
    // Check UserAuth just in case
    $userAuth = UserAuth::where('email', $email)->first();
    if ($userAuth) {
        echo "BUT UserAuth FOUND in 'user_auths' table (ID: {$userAuth->id}). Sync issue?\n";
    }
    exit;
}

echo "User ID: " . $user->id . "\n";
echo "Is Superadmin: " . $user->is_superadmin . "\n";
echo "Company ID: " . $user->company_id . "\n";
echo "User Auth ID: " . $user->user_auth_id . "\n";

if ($user->company) {
    echo "Company Found: " . $user->company->company_name . "\n";
    
    $attendanceSetting = AttendanceSetting::where('company_id', $user->company_id)->first();
    if ($attendanceSetting) {
        echo "--- Attendance Settings ---\n";
        echo "Auto Clock-in: " . $attendanceSetting->auto_clock_in . "\n";
        echo "IP Check: " . $attendanceSetting->ip_check . "\n";
        echo "IP Address: " . $attendanceSetting->ip_address . "\n";
        echo "Radius Check: " . $attendanceSetting->radius_check . "\n";
        echo "Radius: " . $attendanceSetting->radius . "\n";
    } else {
        echo "Attendance Settings NOT FOUND for company.\n";
    }
} else {
    echo "Company NOT FOUND for user.\n";
}

echo "--- App/Session ---\n";
echo "APP_URL: " . config('app.url') . "\n";
echo "APP_ENV: " . config('app.env') . "\n";
echo "APP_NAME(config app.name): " . config('app.name') . "\n";
echo "APP_APP_NAME(config app.app_name): " . config('app.app_name') . "\n";
echo "SESSION_DRIVER: " . config('session.driver') . "\n";
echo "SESSION_DOMAIN: " . (config('session.domain') ?? 'NULL') . "\n";
echo "SESSION_SECURE: " . (is_null(config('session.secure')) ? 'NULL' : (config('session.secure') ? 'true' : 'false')) . "\n";
echo "SESSION_SAMESITE: " . config('session.same_site') . "\n";
echo "CACHE_DRIVER: " . config('cache.default') . "\n";
echo "Cache user_is_active_{$user->id}: " . (cache()->has('user_is_active_' . $user->id) ? 'HIT' : 'MISS') . "\n";
if (cache()->has('user_is_active_' . $user->id)) {
    echo "Cache user_is_active_{$user->id} value: ";
    var_export(cache('user_is_active_' . $user->id));
    echo "\n";
}

echo "--- Global Settings ---\n";
$globalSetting = GlobalSetting::first();
if ($globalSetting) {
    echo "Recaptcha Status: " . $globalSetting->google_recaptcha_status . "\n";
    echo "Recaptcha V2 Status: " . $globalSetting->google_recaptcha_v2_status . "\n";
    echo "Recaptcha V3 Status: " . $globalSetting->google_recaptcha_v3_status . "\n";
    echo "Recaptcha V2 Site Key: " . ($globalSetting->google_recaptcha_v2_site_key ? 'PRESENT' : 'NULL') . "\n";
    echo "Recaptcha V3 Site Key: " . ($globalSetting->google_recaptcha_v3_site_key ? 'PRESENT' : 'NULL') . "\n";
} else {
    echo "GlobalSetting NOT FOUND.\n";
}

echo "--- Module Flags ---\n";
echo "module_enabled(Subdomain): " . (module_enabled('Subdomain') ? 'true' : 'false') . "\n";

echo "--- Auth Configuration ---\n";
echo "Auth Guard 'web' Provider: " . config('auth.guards.web.provider') . "\n";
echo "Auth Provider 'users' Model: " . config('auth.providers.users.model') . "\n";

echo "--- Password Hash Check ---\n";
// Check if UserAuth exists and has password
$userAuth = UserAuth::find($user->user_auth_id);
if ($userAuth) {
    echo "UserAuth Record Found.\n";
    echo "Password Hash Length: " . strlen($userAuth->password) . "\n";
    echo "Email Verified At: " . ($userAuth->email_verified_at ?? 'NULL') . "\n";
    
    echo "Two Factor Secret: " . ($userAuth->two_factor_secret ? "PRESENT" : "NULL") . "\n";
    echo "Two Factor Confirmed: " . $userAuth->two_factor_confirmed . "\n";
    echo "Two Factor Email Confirmed: " . $userAuth->two_factor_email_confirmed . "\n";
    
    try {
        if ($userAuth->two_factor_secret) {
            decrypt($userAuth->two_factor_secret);
            echo "Two Factor Secret Decryption: SUCCESS\n";
        }
    } catch (\Exception $e) {
        echo "Two Factor Secret Decryption: FAILED (" . $e->getMessage() . ")\n";
    }

    echo "APP_KEY: " . substr(config('app.key'), 0, 10) . "...\n";

    echo "--- Login Restrictions ---\n";
    echo "UserAuth Users Count: " . $userAuth->users->count() . "\n";
    echo "Users (status/login/company_id):\n";
    foreach ($userAuth->users as $u) {
        echo "- {$u->id} / {$u->status} / {$u->login} / " . ($u->company_id ?? 'NULL') . "\n";
    }

    try {
        UserAuth::validateLoginActiveDisabled($userAuth);
        echo "validateLoginActiveDisabled: PASS\n";
    } catch (ValidationException $e) {
        echo "validateLoginActiveDisabled: FAIL\n";
        print_r($e->errors());
    } catch (\Exception $e) {
        echo "validateLoginActiveDisabled: ERROR (" . $e->getMessage() . ")\n";
    }
} else {
    echo "UserAuth Record NOT FOUND via user_auth_id!\n";
}
