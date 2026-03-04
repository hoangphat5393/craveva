<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_auths')) {
            DB::table('user_auths')
                ->where('email', 'superadmin@example.com')
                ->update([
                    'password' => Hash::make('12345678'),
                    'updated_at' => now(),
                ]);
        }

        if (Schema::hasTable('users')) {
            $update = [];
            if (Schema::hasColumn('users', 'status')) {
                $update['status'] = 'active';
            }
            if (Schema::hasColumn('users', 'login')) {
                $update['login'] = 'enable';
            }
            if (! empty($update)) {
                DB::table('users')
                    ->where('email', 'superadmin@example.com')
                    ->update($update);
            }
        }
    }

    public function down(): void
    {
        // no-op
    }
};
