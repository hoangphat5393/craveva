#!/bin/bash
cd /var/www/craveva-staging/current/craveva

echo "--- ENV CHECK ---"
grep -E "APP_KEY|APP_URL|SESSION_DOMAIN|DB_DATABASE" .env

echo "--- USER & AUTH CHECK ---"
php artisan tinker --execute='
  $user = App\Models\User::where("email", "hoangphat5393@gmail.com")->first();
  if ($user) {
      echo "User ID: " . $user->id . "\n";
      echo "User Auth ID: " . ($user->user_auth_id ?? "NULL") . "\n";
      
      $auth = null;
      try {
          if ($user->user_auth_id) {
            $auth = DB::table("user_auths")->where("id", $user->user_auth_id)->first();
          }
      } catch (\Exception $e) {
          echo "Error querying user_auths: " . $e->getMessage() . "\n";
      }

      if ($auth) {
          echo "UserAuth Found.\n";
          echo "Password Hash: " . ($auth->password ? "EXISTS" : "EMPTY") . "\n";
          echo "Hash: " . $auth->password . "\n";
      } else {
          echo "UserAuth Record NOT FOUND in DB!\n";
      }
  } else {
      echo "User NOT Found!\n";
  }
'
