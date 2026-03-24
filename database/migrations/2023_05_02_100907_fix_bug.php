<?php

use App\Enums\MaritalStatus;
use App\Models\EmployeeDetails;
use App\Models\User;
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

        Schema::table('user_taskboard_settings', function (Blueprint $table) {
            $foreignKeys = $this->listTableForeignKeys('user_taskboard_settings');

            if (in_array('user_taskboard_settings_board_column_id_foreign', $foreignKeys)) {
                $table->dropForeign(['board_column_id']);
            }

            $table->foreign('board_column_id')->references('id')->on('taskboard_columns')->onDelete('cascade')->onUpdate('cascade');
        });

        DB::statement("ALTER TABLE `users` CHANGE `gender` `gender` ENUM('male','female','others') NULL DEFAULT 'male';");

        User::whereNull('gender')->update(['gender' => 'male']);

        $this->setMaritalStatusDefault();

        EmployeeDetails::whereNull('marital_status')->update(['marital_status' => MaritalStatus::Single]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

    public function listTableForeignKeys($table)
    {
        return array_column(Schema::getConnection()->getSchemaBuilder()->getForeignKeys($table), 'name');
    }

    private function setMaritalStatusDefault(): void
    {
        if (! Schema::hasColumn('employee_details', 'marital_status')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        $default = str_replace("'", "''", MaritalStatus::Single->value);

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `employee_details` MODIFY `marital_status` VARCHAR(255) NULL DEFAULT '{$default}'");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE \"employee_details\" ALTER COLUMN \"marital_status\" TYPE VARCHAR(255)");
            DB::statement("ALTER TABLE \"employee_details\" ALTER COLUMN \"marital_status\" SET DEFAULT '{$default}'");
            DB::statement("ALTER TABLE \"employee_details\" ALTER COLUMN \"marital_status\" DROP NOT NULL");
            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement("ALTER TABLE [employee_details] ADD CONSTRAINT DF_employee_details_marital_status DEFAULT ('{$default}') FOR [marital_status]");
            return;
        }

        throw new \RuntimeException('change() fallback is disabled to avoid doctrine/dbal dependency in this migration.');
    }
};
