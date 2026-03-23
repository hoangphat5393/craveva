<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('client_product_pricing', 'start_date')) {
            Schema::table('client_product_pricing', function (Blueprint $table) {
                $table->dateTime('start_date')->nullable()->after('product_id');
                $table->dateTime('end_date')->nullable()->after('start_date');
            });

            // Update existing records only if we just added the columns
            `\DB`::table('client_product_pricing')->update([
                'start_date' => \DB::raw('created_at'),
                'end_date' => '2099-12-31 23:59:59',
            ]);

            // Handle cases where created_at might be null
            \DB::table('client_product_pricing')->whereNull('start_date')->update([
                'start_date' => now(),
            ]);
        }

        Schema::table('client_product_pricing', function (Blueprint $table) {
            // Ensure columns are not nullable (idempotent change)
            if (Schema::hasColumn('client_product_pricing', 'start_date')) {
                $table->dateTime('start_date')->nullable(false)->change();
                $table->dateTime('end_date')->nullable(false)->change();
            }

            // Drop unique constraint — Laravel 11: không dùng Doctrine; dùng getIndexes()
            $indexNames = array_column(
                Schema::getConnection()->getSchemaBuilder()->getIndexes('client_product_pricing'),
                'name'
            );

            if (! in_array('client_product_pricing_client_id_index', $indexNames, true)) {
                $table->index('client_id', 'client_product_pricing_client_id_index');
            }

            if (in_array('client_product_pricing_client_id_product_id_unique', $indexNames, true)) {
                $table->dropUnique('client_product_pricing_client_id_product_id_unique');
            }

            if (! in_array('client_product_pricing_client_id_product_id_index', $indexNames, true)) {
                $table->index(['client_id', 'product_id'], 'client_product_pricing_client_id_product_id_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_product_pricing', function (Blueprint $table) {
            $table->dropIndex(['client_id', 'product_id']);
            $table->unique(['client_id', 'product_id']);
            $table->dropColumn(['start_date', 'end_date']);
        });
    }
};
