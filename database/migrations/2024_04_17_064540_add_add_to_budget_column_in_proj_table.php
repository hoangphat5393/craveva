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

        if (! Schema::hasColumn('deals', 'deal_watcher')) {
            Schema::table('deals', function (Blueprint $table) {
                $table->integer('deal_watcher')->nullable();
            });
        }

        if (! Schema::hasColumn('project_milestones', 'add_to_budget')) {
            Schema::table('project_milestones', function (Blueprint $table) {
                $table->enum('add_to_budget', ['yes', 'no'])->default('no')->after('status');
            });
        }

        $this->setDecimal('leave_types', 'monthly_limit', 10, 2);
        $this->setDecimal('project_time_logs', 'earnings', 16, 2);

        if (! Schema::hasColumn('attendance_settings', 'qr_enable')) {
            Schema::table('attendance_settings', function (Blueprint $table) {
                $table->enum('qr_enable', ['1', '0'])->default('1');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}

    private function setDecimal(string $table, string $column, int $precision, int $scale): void
    {
        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` DECIMAL({$precision},{$scale}) NULL");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" TYPE NUMERIC({$precision},{$scale})");
            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement("ALTER TABLE [{$table}] ALTER COLUMN [{$column}] DECIMAL({$precision},{$scale}) NULL");
            return;
        }

        throw new \RuntimeException('change() fallback is disabled to avoid doctrine/dbal dependency in this migration.');
    }
};
