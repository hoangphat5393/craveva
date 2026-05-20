<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimates', function (Blueprint $table): void {
            if (! Schema::hasColumn('estimates', 'production_bom_id')) {
                $table->unsignedBigInteger('production_bom_id')->nullable()->after('estimate_request_id');
                $table->index('production_bom_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('estimates', function (Blueprint $table): void {
            if (Schema::hasColumn('estimates', 'production_bom_id')) {
                $table->dropIndex(['production_bom_id']);
                $table->dropColumn('production_bom_id');
            }
        });
    }
};
