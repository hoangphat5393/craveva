<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->string('ai_workspace_agent_id', 32)->nullable();
            $table->string('ai_workspace_api_base', 255)->nullable();
            $table->text('ai_workspace_api_key')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn([
                'ai_workspace_agent_id',
                'ai_workspace_api_base',
                'ai_workspace_api_key',
            ]);
        });
    }
};
