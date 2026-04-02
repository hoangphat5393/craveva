<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stock_movements')) {
            return;
        }

        Schema::table('stock_movements', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_movements', 'idempotency_key')) {
                $table->string('idempotency_key', 100)->nullable()->after('reference_id');
                $table->index(['company_id', 'idempotency_key'], 'stock_movement_company_idempotency_idx');
            }
        });
    }

    public function down(): void
    {
        // Keep rollback non-destructive.
    }
};
