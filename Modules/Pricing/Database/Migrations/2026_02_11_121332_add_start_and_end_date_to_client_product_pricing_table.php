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
        if (!Schema::hasColumn('client_product_pricing', 'start_date')) {
            Schema::table('client_product_pricing', function (Blueprint $table) {
                $table->dateTime('start_date')->nullable()->after('product_id');
                $table->dateTime('end_date')->nullable()->after('start_date');
            });

            // Update existing records only if we just added the columns
            \DB::table('client_product_pricing')->update([
                'start_date' => \DB::raw('created_at'),
                'end_date' => '2099-12-31 23:59:59'
            ]);

            // Handle cases where created_at might be null
            \DB::table('client_product_pricing')->whereNull('start_date')->update([
                'start_date' => now()
            ]);
        }

        Schema::table('client_product_pricing', function (Blueprint $table) {
            // Ensure columns are not nullable (idempotent change)
            if (Schema::hasColumn('client_product_pricing', 'start_date')) {
                 $table->dateTime('start_date')->nullable(false)->change();
                 $table->dateTime('end_date')->nullable(false)->change();
            }

            // Drop unique constraint
            // First, add a regular index on client_id to satisfy FK constraint
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('client_product_pricing');
            
            if (!array_key_exists('client_product_pricing_client_id_index', $indexes)) {
                $table->index('client_id', 'client_product_pricing_client_id_index');
            }

            // Check if unique index exists before dropping
            // Note: Laravel's Schema builder doesn't have hasIndex easily, checking by name convention
            // We'll wrap in try-catch or use raw SQL to be safe if checking via Doctrine is complex
            // But relying on Doctrine list above:
            
            if (array_key_exists('client_product_pricing_client_id_product_id_unique', $indexes)) {
                 $table->dropUnique('client_product_pricing_client_id_product_id_unique');
            }
            
            // Add composite index for performance if not exists
            if (!array_key_exists('client_product_pricing_client_id_product_id_index', $indexes)) {
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
