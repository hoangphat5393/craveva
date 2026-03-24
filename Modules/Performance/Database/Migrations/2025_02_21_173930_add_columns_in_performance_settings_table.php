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
        Schema::table('performance_settings', function (Blueprint $table) {
            $table->enum('meeting_slack_notification', ['yes', 'no'])->default('no')->after('send_email_notification');
            $table->enum('meeting_push_notification', ['yes', 'no'])->default('no')->after('meeting_slack_notification');
            $table->enum('meeting_email_notification', ['yes', 'no'])->default('no')->after('meeting_push_notification');
        });

        $this->renameColumnSafely('performance_settings', 'send_slack_notification', 'objective_slack_notification');
        $this->renameColumnSafely('performance_settings', 'send_push_notification', 'objective_push_notification');
        $this->renameColumnSafely('performance_settings', 'send_email_notification', 'objective_email_notification');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->renameColumnSafely('performance_settings', 'objective_slack_notification', 'send_slack_notification');
        $this->renameColumnSafely('performance_settings', 'objective_push_notification', 'send_push_notification');
        $this->renameColumnSafely('performance_settings', 'objective_email_notification', 'send_email_notification');

        Schema::table('performance_settings', function (Blueprint $table) {
            $table->dropColumn('meeting_slack_notification');
            $table->dropColumn('meeting_push_notification');
            $table->dropColumn('meeting_email_notification');
        });
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
