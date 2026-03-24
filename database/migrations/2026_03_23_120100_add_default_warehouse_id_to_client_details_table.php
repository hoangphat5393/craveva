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
            if (! Schema::hasColumn('client_details', 'default_warehouse_id')) {
                $table->unsignedBigInteger('default_warehouse_id')->nullable()->after('pricing_tier_id');
                $table->index('default_warehouse_id', 'client_details_default_warehouse_idx');
                $table->foreign('default_warehouse_id', 'client_details_default_warehouse_fk')
                    ->references('id')
                    ->on('warehouses')
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('client_details')) {
            return;
        }

        Schema::table('client_details', function (Blueprint $table) {
            if (Schema::hasColumn('client_details', 'default_warehouse_id')) {
                $table->dropForeign('client_details_default_warehouse_fk');
                $table->dropIndex('client_details_default_warehouse_idx');
                $table->dropColumn('default_warehouse_id');
            }
        });
    }
};
