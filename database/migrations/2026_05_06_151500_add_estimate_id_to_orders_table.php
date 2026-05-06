<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'estimate_id')) {
                $table->unsignedBigInteger('estimate_id')->nullable()->after('project_id')->index('orders_estimate_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'estimate_id')) {
                $table->dropColumn('estimate_id');
            }
        });
    }
};
