<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('client_details') || ! Schema::hasColumn('client_details', 'user_id')) {
            return;
        }

        Schema::table('client_details', function (Blueprint $table) {
            // Enforce 1:1 relationship between users and client_details at schema-level
            $table->unique('user_id', 'client_details_user_id_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('client_details')) {
            return;
        }

        Schema::table('client_details', function (Blueprint $table) {
            $table->dropUnique('client_details_user_id_unique');
        });
    }
};
