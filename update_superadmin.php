<?php

use App\Models\User;
use App\Models\UserAuth;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Updating superadmin account...\n";

$email = 'superadmin@example.com';
$password = '12345678';

$user = User::where('is_superadmin', 1)->first();

if (!$user) {
    echo "Superadmin not found in users table.\n";
    // Try to find by email if superadmin flag is missing
    $user = User::where('email', $email)->first();
    if ($user) {
        $user->is_superadmin = 1;
        $user->save();
        echo "Found user by email and promoted to superadmin.\n";
    } else {
         echo "Creating new superadmin...\n";
         // Logic to create new user is complex due to relationships, better to fail or use seeder
         exit(1);
    }
} else {
    echo "Found superadmin: " . $user->email . "\n";
    $user->email = $email;
    $user->save();
    echo "Updated users table email.\n";
}

$userAuth = UserAuth::where('email', $email)->first();

if (!$userAuth && $user->user_auth_id) {
     $userAuth = UserAuth::find($user->user_auth_id);
}

if ($userAuth) {
    $userAuth->email = $email;
    $userAuth->password = Hash::make($password);
    $userAuth->email_verified_at = now();
    $userAuth->save();
    echo "Updated user_auths table email and password.\n";
    
    $user->user_auth_id = $userAuth->id;
    $user->save();
} else {
    echo "Creating new UserAuth...\n";
    $userAuth = UserAuth::create([
        'email' => $email,
        'password' => Hash::make($password),
        'email_verified_at' => now(),
    ]);
    $user->user_auth_id = $userAuth->id;
    $user->save();
    echo "Created and linked UserAuth.\n";
}

echo "Superadmin updated successfully.\n";
echo "Email: $email\n";
echo "Password: $password\n";
