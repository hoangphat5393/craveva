<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('warehouse_sync_reconciliation_logs')) {
            return;
        }

        Schema::create('warehouse_sync_reconciliation_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')->nullable()->index();
            $table->date('report_date')->index();
            $table->string('report_type', 50)->default('daily');
            $table->longText('summary_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Keep rollback non-destructive.
    }
};
