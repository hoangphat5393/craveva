<?php

use App\Enums\MaritalStatus;
use App\Models\EmployeeDetails;
use App\Models\LeaveType;
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
        $this->setStringDefault('employee_details', 'marital_status', MaritalStatus::Single->value, 255);

        EmployeeDetails::withoutGlobalScopes()->where('marital_status', 'unmarried')->update(['marital_status' => MaritalStatus::Single]);

        $leaveTypes = LeaveType::withoutGlobalScopes()->get();

        foreach ($leaveTypes as $leaveType) {
            $maritalStatus = json_decode($leaveType->marital_status);

            if (is_array($maritalStatus)) {
                $maritalStatus = array_map(function ($status) {
                    return $status === 'unmarried' ? MaritalStatus::Single->value : $status;
                }, $maritalStatus);

                $leaveType->marital_status = json_encode($maritalStatus);
                $leaveType->save();
            }
        }
    }

    private function setStringDefault(string $table, string $column, string $default, int $length): void
    {
        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        $escapedDefault = str_replace("'", "''", $default);
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` VARCHAR({$length}) NOT NULL DEFAULT '{$escapedDefault}'");

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" TYPE VARCHAR({$length})");
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" SET DEFAULT '{$escapedDefault}'");
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" SET NOT NULL");

            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement("ALTER TABLE [{$table}] ADD CONSTRAINT DF_{$table}_{$column} DEFAULT ('{$escapedDefault}') FOR [{$column}]");

            return;
        }

        throw new \RuntimeException('change() fallback is disabled to avoid doctrine/dbal dependency in this migration.');
    }
};
