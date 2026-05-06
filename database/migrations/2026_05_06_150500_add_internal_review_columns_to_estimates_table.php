<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimates', function (Blueprint $table): void {
            if (! Schema::hasColumn('estimates', 'president_review_status')) {
                $table->string('president_review_status', 20)->nullable()->after('status');
            }

            if (! Schema::hasColumn('estimates', 'president_reviewed_by')) {
                $table->unsignedInteger('president_reviewed_by')->nullable()->after('president_review_status');
            }

            if (! Schema::hasColumn('estimates', 'president_reviewed_at')) {
                $table->timestamp('president_reviewed_at')->nullable()->after('president_reviewed_by');
            }

            if (! Schema::hasColumn('estimates', 'president_review_note')) {
                $table->text('president_review_note')->nullable()->after('president_reviewed_at');
            }

            if (! Schema::hasColumn('estimates', 'vp_pricing_review_status')) {
                $table->string('vp_pricing_review_status', 20)->nullable()->after('president_review_note');
            }

            if (! Schema::hasColumn('estimates', 'vp_pricing_reviewed_by')) {
                $table->unsignedInteger('vp_pricing_reviewed_by')->nullable()->after('vp_pricing_review_status');
            }

            if (! Schema::hasColumn('estimates', 'vp_pricing_reviewed_at')) {
                $table->timestamp('vp_pricing_reviewed_at')->nullable()->after('vp_pricing_reviewed_by');
            }

            if (! Schema::hasColumn('estimates', 'vp_pricing_review_note')) {
                $table->text('vp_pricing_review_note')->nullable()->after('vp_pricing_reviewed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('estimates', function (Blueprint $table): void {
            $columns = [
                'president_review_status',
                'president_reviewed_by',
                'president_reviewed_at',
                'president_review_note',
                'vp_pricing_review_status',
                'vp_pricing_reviewed_by',
                'vp_pricing_reviewed_at',
                'vp_pricing_review_note',
            ];

            $existing = array_values(array_filter($columns, fn (string $column): bool => Schema::hasColumn('estimates', $column)));

            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};
