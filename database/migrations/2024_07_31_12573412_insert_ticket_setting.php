<?php

use App\Models\Company;
use App\Models\TicketSettingForAgents;
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
        if (Schema::hasTable('ticket_settings_for_agents')) {
            $this->setUnsignedIntNullable('ticket_settings_for_agents', 'user_id', true);

            $companyIds = Company::pluck('id');

            $existingCompanyIds = TicketSettingForAgents::whereIn('company_id', $companyIds)
                ->pluck('company_id')
                ->toArray();

            $newCompanyIds = array_diff($companyIds->toArray(), $existingCompanyIds);

            $insertData = array_map(function ($companyId) {
                return [
                    'ticket_scope' => 'assigned_tickets',
                    'company_id' => $companyId,
                ];
            }, $newCompanyIds);

            if (! empty($insertData)) {
                TicketSettingForAgents::insert($insertData);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->setUnsignedIntNullable('ticket_settings_for_agents', 'user_id', false);
    }

    private function setUnsignedIntNullable(string $table, string $column, bool $nullable): void
    {
        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        $nullSql = $nullable ? 'NULL' : 'NOT NULL';

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` INT UNSIGNED {$nullSql} DEFAULT NULL");
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

        throw new \RuntimeException('change() fallback is disabled to avoid doctrine/dbal dependency in this migration.');
    }
};
