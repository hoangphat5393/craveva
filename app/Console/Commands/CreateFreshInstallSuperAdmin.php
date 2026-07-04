<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class CreateFreshInstallSuperAdmin extends Command
{
    protected $signature = 'fresh-install:create-superadmin
        {email : Email address for the Super Admin}
        {--name=Super Admin : Display name}
        {--password-env=FRESH_ADMIN_PASSWORD : Environment variable containing the password}';

    protected $description = 'Create the first Super Admin without storing credentials in migrations or seed files';

    public function handle(): int
    {
        if (! Schema::hasTable('user_auths') || ! Schema::hasTable('users')) {
            $this->error('The fresh-install schema must be migrated before creating the Super Admin.');

            return self::FAILURE;
        }

        if (DB::table('users')->where('is_superadmin', 1)->exists()) {
            $this->error('A Super Admin already exists; no account was changed.');

            return self::FAILURE;
        }

        $email = trim((string) $this->argument('email'));
        $name = trim((string) $this->option('name'));
        $passwordEnvironmentVariable = trim((string) $this->option('password-env'));
        $password = $passwordEnvironmentVariable !== '' ? getenv($passwordEnvironmentVariable) : false;

        if ($password === false || $password === '') {
            $password = $this->secret('Super Admin password');
        }

        $validation = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $password],
            ['email' => ['required', 'email'], 'name' => ['required', 'string', 'max:191'], 'password' => ['required', 'string', 'min:8']]
        );
        if ($validation->fails()) {
            foreach ($validation->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        if (DB::table('user_auths')->where('email', $email)->exists()
            || DB::table('users')->where('email', $email)->exists()) {
            $this->error('The email already exists; no account was changed.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($email, $name, $password): void {
            $now = now();
            $userAuthId = DB::table('user_auths')->insertGetId([
                'email' => $email,
                'password' => Hash::make((string) $password),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('users')->insert([
                'company_id' => null,
                'user_auth_id' => $userAuthId,
                'is_superadmin' => 1,
                'name' => $name,
                'email' => $email,
                'gender' => 'male',
                'locale' => 'en',
                'status' => 'active',
                'login' => 'enable',
                'email_notifications' => 1,
                'dark_theme' => 0,
                'rtl' => 0,
                'admin_approval' => 1,
                'permission_sync' => 1,
                'google_calendar_status' => 1,
                'customised_permissions' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        $this->info('Super Admin created successfully.');

        return self::SUCCESS;
    }
}
