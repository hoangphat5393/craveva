<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('grn_items')) {
            return;
        }

        Schema::table('grn_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('grn_items', 'qc_status')) {
                $table->string('qc_status', 16)->nullable()->after('picking_rule_applied');
            }
            if (! Schema::hasColumn('grn_items', 'qc_reviewed_by')) {
                $table->unsignedBigInteger('qc_reviewed_by')->nullable()->after('qc_status');
            }
            if (! Schema::hasColumn('grn_items', 'qc_reviewed_at')) {
                $table->timestamp('qc_reviewed_at')->nullable()->after('qc_reviewed_by');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('grn_items')) {
            return;
        }

        Schema::table('grn_items', function (Blueprint $table): void {
            if (Schema::hasColumn('grn_items', 'qc_reviewed_at')) {
                $table->dropColumn('qc_reviewed_at');
            }
            if (Schema::hasColumn('grn_items', 'qc_reviewed_by')) {
                $table->dropColumn('qc_reviewed_by');
            }
            if (Schema::hasColumn('grn_items', 'qc_status')) {
                $table->dropColumn('qc_status');
            }
        });
    }
};
