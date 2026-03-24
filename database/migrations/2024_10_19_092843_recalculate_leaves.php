<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_leave_quotas', function ($table) {
            $table->double('overutilised_leaves')->default(0);
            $table->double('unused_leaves')->default(0);
            $table->double('carry_forward_leaves')->default(0);
            $table->double('carry_forward_applied')->default(0);
        });

        Schema::table('employee_leave_quota_histories', function ($table) {
            $table->double('overutilised_leaves')->default(0);
            $table->double('unused_leaves')->default(0);
            $table->double('carry_forward_leaves')->default(0);
            $table->boolean('carry_forward_applied')->default(0);
        });

        $this->setDoubleNullable('leave_types', 'no_of_leaves', true);

        DB::table('employee_leave_quota_histories')->truncate();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_leave_quota_histories', function ($table) {
            $table->dropColumn(['carry_forward_applied']);
            $table->dropColumn(['carry_forward_leaves']);
            $table->dropColumn(['unused_leaves']);
            $table->dropColumn(['overutilised_leaves']);
        });

        Schema::table('employee_leave_quotas', function ($table) {
            $table->dropColumn(['carry_forward_applied']);
            $table->dropColumn(['carry_forward_leaves']);
            $table->dropColumn(['unused_leaves']);
            $table->dropColumn(['overutilised_leaves']);
        });
    }

    private function setDoubleNullable(string $table, string $column, bool $nullable): void
    {
        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        $nullSql = $nullable ? 'NULL' : 'NOT NULL';

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` DOUBLE {$nullSql}");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" TYPE DOUBLE PRECISION");
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" " . ($nullable ? 'DROP NOT NULL' : 'SET NOT NULL'));
            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement("ALTER TABLE [{$table}] ALTER COLUMN [{$column}] FLOAT {$nullSql}");
            return;
        }

        throw new \RuntimeException('change() fallback is disabled to avoid doctrine/dbal dependency in this migration.');
    }
};
