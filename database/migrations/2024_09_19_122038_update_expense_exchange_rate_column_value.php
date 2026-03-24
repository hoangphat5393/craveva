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
        $this->setUnsignedIntNullable('expenses', 'user_id', true);

        DB::table('expenses')
            ->join('currencies', 'expenses.currency_id', '=', 'currencies.id')
            ->update(['expenses.exchange_rate' => DB::raw('currencies.exchange_rate')]);

        Schema::table('global_settings', function (Blueprint $table) {
            $table->string('dedicated_subdomain')->nullable()->after('currency_key_version'); // Adjust the 'after' field as needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->setUnsignedIntNullable('expenses', 'user_id', false);

        DB::table('expenses')->update(['exchange_rate' => null]);

        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn('dedicated_subdomain');
        });
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

        throw new \RuntimeException('change() fallback is disabled to avoid doctrine/dbal dependency in this migration.');
    }
};
