<?php

use App\Models\Module;
use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $this->setTextNullable('project_time_logs', 'memo', true);

        \App\Models\EmailNotificationSetting::where('slug', 'clock-in-notification')->delete();

        $module = Module::where('module_name', 'reports')->first();

        if (! is_null($module)) {
            $permissionName = 'view_leave_report';

            $permission = Permission::where('name', $permissionName)->update([
                'name' => $permissionName,
                'display_name' => ucwords(str_replace('_', ' ', $permissionName)),
                'is_custom' => 1,
                'module_id' => $module->id,
                'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}

    private function setTextNullable(string $table, string $column, bool $nullable): void
    {
        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        $nullSql = $nullable ? 'NULL' : 'NOT NULL';

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` TEXT {$nullSql}");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" TYPE TEXT");
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" " . ($nullable ? 'DROP NOT NULL' : 'SET NOT NULL'));
            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement("ALTER TABLE [{$table}] ALTER COLUMN [{$column}] NVARCHAR(MAX) {$nullSql}");
            return;
        }

        throw new \RuntimeException('change() fallback is disabled to avoid doctrine/dbal dependency in this migration.');
    }
};
