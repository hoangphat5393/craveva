<?php

use App\Models\Company;
use App\Models\GlobalSetting;
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

        $this->faviconToCompany();

        if (! Schema::hasColumn('log_time_for', 'tracker_reminder')) {
            Schema::table('log_time_for', function (Blueprint $table) {
                $table->boolean('tracker_reminder')->after('approval_required');
                $table->time('time')->nullable();
            });
        }

        if (! Schema::hasColumn('lead_notes', 'details')) {

            $this->setLongTextNullable('lead_notes', 'details', true);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_time_for', function (Blueprint $table) {
            $table->dropColumn('tracker_reminder');
            $table->dropColumn('time');
        });
    }

    private function faviconToCompany()
    {
        if (! Schema::hasColumn('companies', 'favicon')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('favicon')->nullable()->after('logo');
            });

            $globalSetting = GlobalSetting::select('id', 'favicon')->first();

            if ($globalSetting) {
                $company = Company::first();
                $company->favicon = $globalSetting->favicon;
                $company->saveQuietly();
            }
        }
    }

    private function setLongTextNullable(string $table, string $column, bool $nullable): void
    {
        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        $nullSql = $nullable ? 'NULL' : 'NOT NULL';

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` LONGTEXT {$nullSql}");
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
