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
        $columnsWithoutDefault = [
            ['orders', 'sub_total'],
            ['orders', 'total'],
            ['order_items', 'unit_price'],
            ['order_items', 'quantity'],
            ['credit_notes', 'sub_total'],
            ['credit_notes', 'total'],
            ['credit_note_items', 'unit_price'],
            ['credit_note_items', 'amount'],
            ['invoices', 'total'],
            ['invoices', 'sub_total'],
            ['invoice_items', 'quantity'],
            ['invoice_items', 'unit_price'],
            ['invoice_items', 'amount'],
            ['quotations', 'sub_total'],
            ['quotations', 'total'],
            ['quotation_items', 'amount'],
            ['estimates', 'sub_total'],
            ['estimates', 'total'],
            ['estimate_templates', 'sub_total'],
            ['estimate_templates', 'total'],
            ['estimate_templates', 'discount'],
            ['estimate_items', 'quantity'],
            ['estimate_items', 'unit_price'],
            ['estimate_items', 'amount'],
            ['estimate_template_items', 'quantity'],
            ['estimate_template_items', 'unit_price'],
            ['estimate_template_items', 'amount'],
            ['expenses', 'price'],
            ['project_milestones', 'cost'],
            ['proposals', 'sub_total'],
            ['proposals', 'total'],
            ['proposals', 'discount'],
            ['proposal_items', 'quantity'],
            ['proposal_items', 'unit_price'],
            ['proposal_items', 'amount'],
            ['proposal_templates', 'sub_total'],
            ['proposal_templates', 'total'],
            ['proposal_templates', 'discount'],
            ['proposal_template_items', 'unit_price'],
            ['proposal_template_items', 'amount'],
            ['proposal_template_items', 'quantity'],
            ['order_carts', 'quantity'],
            ['order_carts', 'unit_price'],
            ['order_carts', 'amount'],
            ['expenses_recurring', 'price'],
            ['invoice_recurring_items', 'quantity'],
            ['invoice_recurring_items', 'unit_price'],
            ['invoice_recurring_items', 'amount'],
            ['payments', 'amount'],
        ];

        foreach ($columnsWithoutDefault as [$table, $column]) {
            $this->setDecimalNullableWithDefault($table, $column, 30, 2, false, null);
        }

        $columnsDefaultZero = [
            ['orders', 'discount'],
            ['credit_notes', 'discount'],
            ['invoices', 'due_amount'],
            ['invoices', 'discount'],
            ['estimates', 'discount'],
            ['invoice_recurring', 'sub_total'],
            ['invoice_recurring', 'total'],
            ['invoice_recurring', 'discount'],
        ];

        foreach ($columnsDefaultZero as [$table, $column]) {
            $this->setDecimalNullableWithDefault($table, $column, 30, 2, false, '0');
        }

        $nullableColumns = [
            ['credit_notes', 'adjustment_amount', null],
            ['bank_accounts', 'bank_balance', null],
            ['bank_accounts', 'opening_balance', null],
            ['bank_transactions', 'bank_balance', null],
            ['bank_transactions', 'amount', null],
            ['projects', 'project_budget', null],
        ];

        foreach ($nullableColumns as [$table, $column, $default]) {
            $this->setDecimalNullableWithDefault($table, $column, 30, 2, true, $default);
        }

        if (Schema::hasColumn('leads', 'value')) {
            $this->setDecimalNullableWithDefault('leads', 'value', 30, 2, true, '0');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

    private function setDecimalNullableWithDefault(
        string $table,
        string $column,
        int $precision,
        int $scale,
        bool $nullable,
        ?string $default
    ): void {
        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        $nullSql = $nullable ? 'NULL' : 'NOT NULL';

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $defaultSql = $default !== null ? " DEFAULT {$default}" : '';
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` DECIMAL({$precision},{$scale}) {$nullSql}{$defaultSql}");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" TYPE NUMERIC({$precision},{$scale})");
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" " . ($nullable ? 'DROP NOT NULL' : 'SET NOT NULL'));
            if ($default !== null) {
                DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" SET DEFAULT {$default}");
            } else {
                DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" DROP DEFAULT");
            }
            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement("ALTER TABLE [{$table}] ALTER COLUMN [{$column}] DECIMAL({$precision},{$scale}) {$nullSql}");
            return;
        }

        throw new \RuntimeException('change() fallback is disabled to avoid doctrine/dbal dependency in this migration.');
    }
};
