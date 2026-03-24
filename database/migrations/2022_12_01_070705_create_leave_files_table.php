<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_files', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->unsignedInteger('user_id')->index('leave_files_user_id_foreign');
            $table->unsignedInteger('leave_id')->index('leave_files_leave_id_foreign');
            $table->string('filename');
            $table->string('hashname')->nullable();
            $table->string('size')->nullable();
            $table->unsignedInteger('added_by')->nullable()->index('leave_files_added_by_foreign');
            $table->unsignedInteger('last_updated_by')->nullable()->index('leave_files_last_updated_by_foreign');
            $table->foreign(['added_by'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['last_updated_by'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['leave_id'])->references(['id'])->on('leaves')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->timestamps();
        });

        $this->setDoubleNullable('employee_leave_quotas', 'no_of_leaves', false);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leave_files');
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
