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
        if (Schema::hasTable('company_customer_pricing')) {

            // Step 1: Rename and Cleanup
            if (Schema::hasColumn('company_customer_pricing', 'customer_company_id')) {
                Schema::table('company_customer_pricing', function (Blueprint $table) {
                    $foreignKeys = Schema::getConnection()->getSchemaBuilder()->getForeignKeys('company_customer_pricing');
                    foreach ($foreignKeys as $fk) {
                        if (str_contains($fk['name'], 'customer_company_id')) {
                            $table->dropForeign($fk['name']);
                        }
                    }

                    try {
                        $table->dropUnique(['company_id', 'customer_company_id']);
                    } catch (\Exception $e) {
                    }
                });
                $this->renameColumnSafely('company_customer_pricing', 'customer_company_id', 'client_id');
            }

            // Step 2: Ensure correct type and add constraints
            if (Schema::hasColumn('company_customer_pricing', 'client_id')) {
                Schema::table('company_customer_pricing', function (Blueprint $table) {
                    // Force type to unsignedInteger to match users.id
                    // This fixes the issue if it was accidentally changed to BigInt
                    $table->foreign('client_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

                    try {
                        $table->unique(['company_id', 'client_id']);
                    } catch (\Exception $e) {
                    }
                });
                $this->setUnsignedIntNullable('company_customer_pricing', 'client_id');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('company_customer_pricing')) {
            if (Schema::hasColumn('company_customer_pricing', 'client_id')) {
                Schema::table('company_customer_pricing', function (Blueprint $table) {
                    $foreignKeys = Schema::getConnection()->getSchemaBuilder()->getForeignKeys('company_customer_pricing');
                    foreach ($foreignKeys as $fk) {
                        if (str_contains($fk['name'], 'client_id')) {
                            $table->dropForeign($fk['name']);
                        }
                    }

                    try {
                        $table->dropUnique(['company_id', 'client_id']);
                    } catch (\Exception $e) {
                    }
                });
                $this->renameColumnSafely('company_customer_pricing', 'client_id', 'customer_company_id');
            }

            if (Schema::hasColumn('company_customer_pricing', 'customer_company_id')) {
                Schema::table('company_customer_pricing', function (Blueprint $table) {
                    $table->foreign('customer_company_id')->references('id')->on('companies')->onUpdate('cascade')->onDelete('cascade');
                    $table->unique(['company_id', 'customer_company_id']);
                });
                $this->setUnsignedIntNullable('company_customer_pricing', 'customer_company_id');
            }
        }
    }

    private function renameColumnSafely(string $table, string $from, string $to): void
    {
        if (! Schema::hasColumn($table, $from) || Schema::hasColumn($table, $to)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `{$table}` RENAME COLUMN `{$from}` TO `{$to}`");

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE \"{$table}\" RENAME COLUMN \"{$from}\" TO \"{$to}\"");

            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement("EXEC sp_rename '{$table}.{$from}', '{$to}', 'COLUMN'");

            return;
        }

        throw new \RuntimeException('renameColumn fallback is disabled to avoid doctrine/dbal dependency in this migration.');
    }

    private function setUnsignedIntNullable(string $table, string $column): void
    {
        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` INT UNSIGNED NULL");

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" TYPE INTEGER");
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" DROP NOT NULL");

            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement("ALTER TABLE [{$table}] ALTER COLUMN [{$column}] INT NULL");

            return;
        }

        throw new \RuntimeException('change() fallback is disabled to avoid doctrine/dbal dependency in this migration.');
    }
};
