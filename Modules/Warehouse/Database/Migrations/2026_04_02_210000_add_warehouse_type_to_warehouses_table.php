<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('warehouses')) {
            return;
        }

        Schema::table('warehouses', function (Blueprint $table) {
            if (! Schema::hasColumn('warehouses', 'warehouse_type')) {
                $table->string('warehouse_type', 20)->default('normal')->after('code');
                $table->index(['company_id', 'warehouse_type'], 'warehouses_company_type_idx');
            }
        });
    }

    public function down(): void
    {
        // Intentionally keep data-safe downgrade behavior.
    }
};
