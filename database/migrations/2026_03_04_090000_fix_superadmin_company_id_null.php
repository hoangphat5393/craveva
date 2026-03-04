<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (! Schema::hasColumn('users', 'is_superadmin') || ! Schema::hasColumn('users', 'company_id')) {
            return;
        }

        DB::table('users')
            ->where('is_superadmin', 1)
            ->whereNotNull('company_id')
            ->update(['company_id' => null]);
    }

    public function down(): void
    {
        // no-op
    }
};
