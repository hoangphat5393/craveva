<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('client_details')) {
            Schema::table('client_details', function (Blueprint $table) {
                if (! Schema::hasColumn('client_details', 'client_code')) {
                    $table->string('client_code', 100)->nullable()->unique()->after('company_name');
                }

                if (! Schema::hasColumn('client_details', 'pricing_tier_id')) {
                    $table->unsignedBigInteger('pricing_tier_id')->nullable()->after('client_code');
                    $table->foreign('pricing_tier_id')->references('id')->on('pricing_tiers')->onUpdate('cascade')->onDelete('SET NULL');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('client_details')) {
            Schema::table('client_details', function (Blueprint $table) {
                if (Schema::hasColumn('client_details', 'pricing_tier_id')) {
                    $table->dropForeign(['pricing_tier_id']);
                    $table->dropColumn('pricing_tier_id');
                }

                if (Schema::hasColumn('client_details', 'client_code')) {
                    $table->dropColumn('client_code');
                }
            });
        }
    }
};
