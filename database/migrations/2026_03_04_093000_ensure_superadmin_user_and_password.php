<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_auths')) {
            return;
        }

        // Ensure superadmin user_auth exists and set a known password
        $userAuth = DB::table('user_auths')->where('email', 'superadmin@example.com')->first();

        if ($userAuth) {
            DB::table('user_auths')
                ->where('id', $userAuth->id)
                ->update([
                    'password' => Hash::make('superadmin'),
                ]);
        } else {
            // Create user_auth if missing
            $userAuthId = DB::table('user_auths')->insertGetId([
                'email' => 'superadmin@example.com',
                'password' => Hash::make('superadmin'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $userAuth = (object)['id' => $userAuthId, 'email' => 'superadmin@example.com'];
        }

        // Ensure users table has a superadmin linked to user_auth
        if (Schema::hasTable('users')) {
            $existing = DB::table('users')->where('email', 'superadmin@example.com')->first();

            $data = [];
            if (Schema::hasColumn('users', 'name')) {
                $data['name'] = 'Super Admin';
            }
            if (Schema::hasColumn('users', 'email')) {
                $data['email'] = 'superadmin@example.com';
            }
            if (Schema::hasColumn('users', 'is_superadmin')) {
                $data['is_superadmin'] = 1;
            }
            if (Schema::hasColumn('users', 'status')) {
                $data['status'] = 'active';
            }
            if (Schema::hasColumn('users', 'company_id')) {
                $data['company_id'] = null;
            }
            if (Schema::hasColumn('users', 'user_auth_id')) {
                $data['user_auth_id'] = $userAuth->id;
            }
            if (Schema::hasColumn('users', 'created_at')) {
                $data['created_at'] = now();
            }
            if (Schema::hasColumn('users', 'updated_at')) {
                $data['updated_at'] = now();
            }

            if ($existing) {
                DB::table('users')
                    ->where('id', $existing->id)
                    ->update($data);
            } else {
                DB::table('users')->insert($data);
            }
        }
    }

    public function down(): void
    {
        // no-op
    }
};
