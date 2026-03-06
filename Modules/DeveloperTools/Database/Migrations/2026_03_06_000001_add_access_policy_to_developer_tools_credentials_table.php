<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('developer_tools_credentials', function (Blueprint $table) {
            if (! Schema::hasColumn('developer_tools_credentials', 'allowed_modules')) {
                $table->json('allowed_modules')->nullable()->after('db_database');
            }
            if (! Schema::hasColumn('developer_tools_credentials', 'created_views_count')) {
                $table->unsignedInteger('created_views_count')->nullable()->after('allowed_modules');
            }
            if (! Schema::hasColumn('developer_tools_credentials', 'generation_duration_ms')) {
                $table->unsignedInteger('generation_duration_ms')->nullable()->after('created_views_count');
            }
            if (! Schema::hasColumn('developer_tools_credentials', 'last_generated_at')) {
                $table->timestamp('last_generated_at')->nullable()->after('generation_duration_ms');
            }
            if (! Schema::hasColumn('developer_tools_credentials', 'last_generation_warnings')) {
                $table->longText('last_generation_warnings')->nullable()->after('last_generated_at');
            }
        });
    }

    public function down(): void {}
};
