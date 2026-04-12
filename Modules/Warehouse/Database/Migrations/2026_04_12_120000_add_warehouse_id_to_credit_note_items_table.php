<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('credit_note_items')) {
            return;
        }

        if (! Schema::hasColumn('credit_note_items', 'warehouse_id')) {
            Schema::table('credit_note_items', function (Blueprint $table) {
                $after = Schema::hasColumn('credit_note_items', 'product_id') ? 'product_id' : 'type';
                $table->unsignedBigInteger('warehouse_id')->nullable()->after($after);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('credit_note_items') && Schema::hasColumn('credit_note_items', 'warehouse_id')) {
            Schema::table('credit_note_items', function (Blueprint $table) {
                $table->dropColumn('warehouse_id');
            });
        }
    }
};
