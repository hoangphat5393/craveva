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
        if (Schema::hasTable('lead_files')) {
            Schema::rename('lead_files', 'deal_files');

            try {
                Schema::table('deal_files', function (Blueprint $table) {
                    $table->dropForeign(['lead_id']);
                });
            } catch (\Exception $e) {
                echo "\nForeign key lead_id does not exist in lead_files\n";
            }

            $this->renameColumnSafely('deal_files', 'lead_id', 'deal_id');

            Schema::table('deal_files', function (Blueprint $table) {
                $table->foreign('deal_id')->references('id')->on('deals')->onDelete('cascade')->onUpdate('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
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
