<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Client code is unique per company (each company has its own client/customer range).
     */
    public function up(): void
    {
        if (! Schema::hasTable('client_details') || ! Schema::hasColumn('client_details', 'client_code')) {
            return;
        }

        try {
            Schema::table('client_details', function (Blueprint $table) {
                $table->dropUnique(['client_code']);
            });
        } catch (\Throwable $e) {
            // Index may not exist if Pricing migration not run or already altered
        }

        Schema::table('client_details', function (Blueprint $table) {
            $table->unique(['company_id', 'client_code'], 'client_details_company_client_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('client_details')) {
            return;
        }

        Schema::table('client_details', function (Blueprint $table) {
            $table->dropUnique('client_details_company_client_code_unique');
        });

        if (Schema::hasColumn('client_details', 'client_code')) {
            Schema::table('client_details', function (Blueprint $table) {
                $table->unique('client_code');
            });
        }
    }
};
