<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->renameColumnSafely('deal_histories', 'deal_stage_id', 'deal_stage_from_id');

        Schema::table('deal_histories', function (Blueprint $table) {
            $table->unsignedInteger('deal_stage_to_id')->nullable();
            $table->foreign('deal_stage_to_id')->references('id')->on('pipeline_stages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deal_histories', function (Blueprint $table) {
            $table->dropForeign(['deal_stage_to_id']);
            $table->dropColumn('deal_stage_to_id');
        });

        $this->renameColumnSafely('deal_histories', 'deal_stage_from_id', 'deal_stage_id');
    }

    private function renameColumnSafely(string $table, string $from, string $to): void
    {
        if (! Schema::hasColumn($table, $from) || Schema::hasColumn($table, $to)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `{$table}` RENAME COLUMN `{$from}` TO `{$to}`");

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE \"{$table}\" RENAME COLUMN \"{$from}\" TO \"{$to}\"");

            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement("EXEC sp_rename '{$table}.{$from}', '{$to}', 'COLUMN'");

            return;
        }

        throw new \RuntimeException('renameColumn fallback is disabled to avoid doctrine/dbal dependency in this migration.');
    }
};
