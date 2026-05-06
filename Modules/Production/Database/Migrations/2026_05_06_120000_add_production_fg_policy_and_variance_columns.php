<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('production_company_fg_policies')) {
            Schema::create('production_company_fg_policies', function (Blueprint $table): void {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->string('policy_mode', 16)->default('controlled');
                $table->decimal('tolerance_percent', 10, 4)->default(5);
                $table->decimal('tolerance_absolute', 15, 4)->default(0);
                $table->boolean('controlled_require_reason_beyond_tolerance')->default(true);
                $table->boolean('controlled_block_beyond_tolerance')->default(false);
                $table->timestamps();

                $table->unique('company_id');
                $table->foreign('company_id')->references('id')->on('companies')->cascadeOnUpdate()->cascadeOnDelete();
            });
        }

        Schema::table('production_batch_outputs', function (Blueprint $table): void {
            if (! Schema::hasColumn('production_batch_outputs', 'variance_reason')) {
                $table->text('variance_reason')->nullable()->after('posted_at');
            }
            if (! Schema::hasColumn('production_batch_outputs', 'variance_from_planned_total')) {
                $table->decimal('variance_from_planned_total', 15, 4)->nullable()->after('variance_reason');
            }
            if (! Schema::hasColumn('production_batch_outputs', 'variance_from_planned_percent')) {
                $table->decimal('variance_from_planned_percent', 15, 4)->nullable()->after('variance_from_planned_total');
            }
            if (! Schema::hasColumn('production_batch_outputs', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('variance_from_planned_percent');
            }
            if (! Schema::hasColumn('production_batch_outputs', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('production_batch_outputs', function (Blueprint $table): void {
            if (Schema::hasColumn('production_batch_outputs', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('production_batch_outputs', 'approved_by')) {
                $table->dropColumn('approved_by');
            }
            if (Schema::hasColumn('production_batch_outputs', 'variance_from_planned_percent')) {
                $table->dropColumn('variance_from_planned_percent');
            }
            if (Schema::hasColumn('production_batch_outputs', 'variance_from_planned_total')) {
                $table->dropColumn('variance_from_planned_total');
            }
            if (Schema::hasColumn('production_batch_outputs', 'variance_reason')) {
                $table->dropColumn('variance_reason');
            }
        });

        Schema::dropIfExists('production_company_fg_policies');
    }
};
