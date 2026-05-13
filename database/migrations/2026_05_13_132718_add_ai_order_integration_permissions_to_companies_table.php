<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('ai_order_integration_allow_create')->default(true);
            $table->boolean('ai_order_integration_allow_read')->default(false);
            $table->boolean('ai_order_integration_allow_update')->default(false);
            $table->boolean('ai_order_integration_allow_delete')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'ai_order_integration_allow_create',
                'ai_order_integration_allow_read',
                'ai_order_integration_allow_update',
                'ai_order_integration_allow_delete',
            ]);
        });
    }
};
