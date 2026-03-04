<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure frontend is not forced to login-only
        if (Schema::hasTable('global_settings') && Schema::hasColumn('global_settings', 'frontend_disable')) {
            DB::table('global_settings')->update(['frontend_disable' => 0]);
        }

        // Ensure FrontDetail record exists to avoid null locale error
        if (Schema::hasTable('front_details')) {
            $exists = DB::table('front_details')->exists();
            if (! $exists) {
                DB::table('front_details')->insert([
                    'get_started_show' => 'yes',
                    'sign_in_show' => 'yes',
                    'locale' => 'en',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // no-op
    }
};
