<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('client_details')) {
            return;
        }

        Schema::table('client_details', function (Blueprint $table) {
            if (! Schema::hasColumn('client_details', 'payment_terms')) {
                $table->string('payment_terms', 255)->nullable();
            }
            if (! Schema::hasColumn('client_details', 'customer_grade')) {
                $table->string('customer_grade', 255)->nullable();
            }
            if (! Schema::hasColumn('client_details', 'channel_type')) {
                $table->string('channel_type', 255)->nullable();
            }
            if (! Schema::hasColumn('client_details', 'business_type')) {
                $table->string('business_type', 255)->nullable();
            }
            if (! Schema::hasColumn('client_details', 'business_closure_date')) {
                $table->date('business_closure_date')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('client_details')) {
            return;
        }

        Schema::table('client_details', function (Blueprint $table) {
            foreach (['payment_terms', 'customer_grade', 'channel_type', 'business_type', 'business_closure_date'] as $col) {
                if (Schema::hasColumn('client_details', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
