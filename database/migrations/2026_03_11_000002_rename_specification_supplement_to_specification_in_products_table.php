<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Rename specification_supplement to specification (規格).
     */
    public function up(): void
    {
        $this->renameColumnSafely('products', 'specification_supplement', 'specification');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->renameColumnSafely('products', 'specification', 'specification_supplement');
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
