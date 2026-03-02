<?php
// Mock REMOTE_ADDR for IpUtils
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\UserAuth;

$email = 'hoangphat5393@gmail.com';
echo "Checking user with email: $email\n";

$userAuth = UserAuth::where('email', $email)->first();

if ($userAuth) {
    echo "UserAuth found: ID {$userAuth->id}, Email: {$userAuth->email}\n";
    $user = User::where('user_auth_id', $userAuth->id)->first();
    if ($user) {
        echo "User found: ID {$user->id}, Company ID: {$user->company_id}\n";
    } else {
        echo "User NOT found for UserAuth ID {$userAuth->id}\n";
    }
} else {
    echo "UserAuth NOT found for email $email\n";
    // Check User table directly
    $user = User::where('email', $email)->first();
    if ($user) {
         echo "User found in users table (legacy/orphan?): ID {$user->id}\n";
    }
}
