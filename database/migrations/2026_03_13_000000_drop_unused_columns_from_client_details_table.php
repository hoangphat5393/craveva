<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Drop legacy columns mobile and office_phone from client_details.
     * App uses users.mobile for personal phone and client_details.office for office phone.
     * See: FUNC_LOGIC/FLOW_ADD_CLIENT.md §9.5
     *
     * Impact on DeveloperTools gateway DBs (api_gateway_*): views on client_details were
     * created with SELECT * and will reference the dropped columns. Queries against those
     * views will fail until views are recreated. Regenerate the credential (Revoke + Generate
     * Credential) in Developer Tools to recreate all views with the new schema.
     */
    public function up(): void
    {
        if (Schema::hasTable('client_details')) {
            Schema::table('client_details', function (Blueprint $table) {
                if (Schema::hasColumn('client_details', 'mobile')) {
                    $table->dropColumn('mobile');
                }
                if (Schema::hasColumn('client_details', 'office_phone')) {
                    $table->dropColumn('office_phone');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('client_details')) {
            Schema::table('client_details', function (Blueprint $table) {
                if (! Schema::hasColumn('client_details', 'mobile')) {
                    $table->string('mobile')->nullable()->after('office');
                }
                if (! Schema::hasColumn('client_details', 'office_phone')) {
                    $table->string('office_phone')->nullable()->after('mobile');
                }
            });
        }
    }
};
