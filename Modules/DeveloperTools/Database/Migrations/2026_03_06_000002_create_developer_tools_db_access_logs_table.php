<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('developer_tools_db_access_logs')) {
            Schema::create('developer_tools_db_access_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id')->index();
                $table->string('db_username')->nullable()->index();
                $table->string('db_database')->nullable()->index();
                $table->json('requested_modules')->nullable();
                $table->unsignedInteger('allowed_tables_count')->nullable();
                $table->unsignedInteger('created_views_count')->nullable();
                $table->unsignedInteger('duration_ms')->nullable();
                $table->enum('status', ['success', 'failed'])->default('success')->index();
                $table->text('warnings')->nullable();
                $table->text('error_message')->nullable();
                $table->unsignedInteger('created_by')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void {}
};
