<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->longText('ai_workspace_embed_code')->nullable();
            $table->longText('ai_assistant_widget_embed_code')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn([
                'ai_workspace_embed_code',
                'ai_assistant_widget_embed_code',
            ]);
        });
    }
};
