<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('developer_tools_credentials') && ! Schema::hasColumn('developer_tools_credentials', 'allowed_tables')) {
            Schema::table('developer_tools_credentials', function (Blueprint $table) {
                $table->json('allowed_tables')->nullable()->after('allowed_modules');
            });
        }

        if (Schema::hasTable('developer_tools_db_access_logs') && ! Schema::hasColumn('developer_tools_db_access_logs', 'allowed_tables')) {
            Schema::table('developer_tools_db_access_logs', function (Blueprint $table) {
                $table->json('allowed_tables')->nullable()->after('requested_modules');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('developer_tools_credentials') && Schema::hasColumn('developer_tools_credentials', 'allowed_tables')) {
            Schema::table('developer_tools_credentials', function (Blueprint $table) {
                $table->dropColumn('allowed_tables');
            });
        }

        if (Schema::hasTable('developer_tools_db_access_logs') && Schema::hasColumn('developer_tools_db_access_logs', 'allowed_tables')) {
            Schema::table('developer_tools_db_access_logs', function (Blueprint $table) {
                $table->dropColumn('allowed_tables');
            });
        }
    }
};
