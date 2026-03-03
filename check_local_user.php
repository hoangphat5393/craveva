<?php
// check_local_user.php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'hoangphat5393@gmail.com')->with('userAuth')->first();

if ($user) {
    echo "User Found: {$user->email}\n";
    echo "User Auth ID: " . ($user->user_auth_id ?? "NULL") . "\n";
    
    if ($user->userAuth) {
        echo "UserAuth Found.\n";
        echo "Password Hash: " . ($user->userAuth->password ? "EXISTS ({$user->userAuth->password})" : "EMPTY") . "\n";
    } else {
        echo "UserAuth Relation is NULL!\n";
        // Try direct query in case relationship is broken
        if ($user->user_auth_id) {
            $auth = DB::table('user_auths')->where('id', $user->user_auth_id)->first();
            if ($auth) {
                echo "Direct DB Check: UserAuth Found. Password: " . ($auth->password ? "EXISTS" : "EMPTY") . "\n";
            } else {
                echo "Direct DB Check: UserAuth ID {$user->user_auth_id} NOT FOUND in user_auths table.\n";
            }
        }
    }
} else {
    echo "User NOT Found locally!\n";
}
