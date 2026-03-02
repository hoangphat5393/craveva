<?php
// Mock REMOTE_ADDR for IpUtils
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\UserAuth;
use Illuminate\Support\Facades\Hash;

$email = 'hoangphat5393@gmail.com';
$password = '12345678';

echo "Checking user with email: $email\n";

$userAuth = UserAuth::where('email', $email)->first();

if ($userAuth) {
    echo "UserAuth found: ID {$userAuth->id}\n";
    if (Hash::check($password, $userAuth->password)) {
        echo "Password MATCHES.\n";
    } else {
        echo "Password DOES NOT MATCH.\n";
        // Reset password to 12345678 for testing if needed (optional, better not touch unless requested)
        // $userAuth->password = Hash::make($password);
        // $userAuth->save();
        // echo "Password reset to $password\n";
    }
} else {
    echo "UserAuth NOT found.\n";
}
