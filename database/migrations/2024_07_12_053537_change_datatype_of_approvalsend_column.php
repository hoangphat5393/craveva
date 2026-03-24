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
        $this->setApprovalSendBoolean('tasks');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->setApprovalSendEnum('tasks');
    }

    private function setApprovalSendBoolean(string $table): void
    {
        if (! Schema::hasColumn($table, 'approval_send')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `approval_send` TINYINT(1) NOT NULL DEFAULT 0");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"approval_send\" TYPE BOOLEAN USING (\"approval_send\"::text IN ('1','true','t'))");
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"approval_send\" SET DEFAULT FALSE");
            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement("ALTER TABLE [{$table}] ALTER COLUMN [approval_send] BIT NOT NULL");
            return;
        }

        throw new \RuntimeException('change() fallback is disabled to avoid doctrine/dbal dependency in this migration.');
    }

    private function setApprovalSendEnum(string $table): void
    {
        if (! Schema::hasColumn($table, 'approval_send')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `approval_send` ENUM('0','1') NOT NULL DEFAULT '0'");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"approval_send\" TYPE VARCHAR(1)");
            DB::statement("UPDATE \"{$table}\" SET \"approval_send\" = CASE WHEN \"approval_send\"::text IN ('1','true','t') THEN '1' ELSE '0' END");
            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement("ALTER TABLE [{$table}] ALTER COLUMN [approval_send] NVARCHAR(1) NOT NULL");
            return;
        }

        throw new \RuntimeException('change() fallback is disabled to avoid doctrine/dbal dependency in this migration.');
    }
};
