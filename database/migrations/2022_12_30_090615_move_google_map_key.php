<?php

use App\Models\Company;
use App\Models\GlobalSetting;
use App\Models\Team;
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

        $this->googleMapKey();
        $this->subTotalTables();
        $this->lastViewed();
        $this->faviconToCompany();
        $this->noteDetailsToText();

        Team::where('parent_id', 0)->update(['parent_id' => null]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

    private function subTotalTables()
    {
        $subTotalTables = ['proposal_templates', 'orders', 'quotations'];

        foreach ($subTotalTables as $tableName) {
            $this->setDecimalNullable($tableName, 'sub_total', 16, 2, false);
        }
    }

    private function googleMapKey()
    {
        if (! Schema::hasColumn('global_settings', 'google_map_key')) {
            Schema::table('global_settings', function (Blueprint $table) {
                $table->string('google_map_key')->nullable();
            });
        }

        $company = Company::first();

        if ($company) {
            $globalSetting = GlobalSetting::first();
            $globalSetting->google_map_key = $company->google_map_key;
            $globalSetting->saveQuietly();
        }
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

    private function lastViewed()
    {
        // Add last viewed and other info
        $lastViewedTables = ['proposals', 'invoices', 'estimates'];

        foreach ($lastViewedTables as $tableName) {
            if (! Schema::hasColumn($tableName, 'last_viewed')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->timestamp('last_viewed')->nullable();
                    $table->string('ip_address')->nullable();
                });
            }
        }
    }

    private function noteDetailsToText()
    {
        $this->setTextNullable('lead_notes', 'details', true);
    }

    private function setDecimalNullable(string $table, string $column, int $precision, int $scale, bool $nullable): void
    {
        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        $nullSql = $nullable ? 'NULL' : 'NOT NULL';

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` DECIMAL({$precision},{$scale}) {$nullSql}");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" TYPE NUMERIC({$precision},{$scale})");
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" " . ($nullable ? 'DROP NOT NULL' : 'SET NOT NULL'));
            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement("ALTER TABLE [{$table}] ALTER COLUMN [{$column}] DECIMAL({$precision},{$scale}) {$nullSql}");
            return;
        }

        throw new \RuntimeException('change() fallback is disabled to avoid doctrine/dbal dependency in this migration.');
    }

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
