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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'storage_condition')) {
                $table->string('storage_condition')->nullable()->after('price');
            }
            if (!Schema::hasColumn('products', 'certification')) {
                $table->text('certification')->nullable()->after('storage_condition');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'storage_condition')) {
                $table->dropColumn('storage_condition');
            }
            if (Schema::hasColumn('products', 'certification')) {
                $table->dropColumn('certification');
            }
        });
    }
};
