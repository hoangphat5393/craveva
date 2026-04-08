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
        // Column must be nullable before ON DELETE SET NULL (MySQL error 1830 otherwise).
        $this->setUnsignedIntNullable('recruit_jobs', 'recruiter_id', true);

        Schema::table('recruit_jobs', function (Blueprint $table) {
            $table->foreign('recruiter_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruit_jobs', function (Blueprint $table) {
            $table->dropForeign(['recruiter_id']);
        });
        $this->setUnsignedIntNullable('recruit_jobs', 'recruiter_id', false);
    }

    private function setUnsignedIntNullable(string $table, string $column, bool $nullable): void
    {
        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        $nullSql = $nullable ? 'NULL' : 'NOT NULL';

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` INT UNSIGNED {$nullSql}");

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" TYPE INTEGER");
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" " . ($nullable ? 'DROP NOT NULL' : 'SET NOT NULL'));

            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement("ALTER TABLE [{$table}] ALTER COLUMN [{$column}] INT {$nullSql}");

            return;
        }

        throw new RuntimeException('change() fallback is disabled to avoid doctrine/dbal dependency in this migration.');
    }
};
