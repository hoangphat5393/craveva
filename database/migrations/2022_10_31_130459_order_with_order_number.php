<?php

use App\Models\Company;
use App\Models\Order;
use App\Models\Ticket;
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
        Schema::table('orders', function (Blueprint $table) {
            $table->bigInteger('order_number')->after('id')->nullable();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->bigInteger('ticket_number')->after('id')->nullable();
        });

        $companies = Company::select('id')->get();

        foreach ($companies as $company) {

            $orders = Order::where('company_id', $company->id)->get();

            foreach ($orders as $key => $order) {
                $order->order_number = $key + 1;
                $order->saveQuietly();
            }

            $tickets = Ticket::where('company_id', $company->id)->get();

            foreach ($tickets as $key => $ticket) {
                $ticket->ticket_number = $key + 1;
                $ticket->saveQuietly();
            }
        }

        $this->setBigIntNullable('invoices', 'invoice_number', false);
        $this->setBigIntNullable('estimates', 'estimate_number', false);
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

    private function setBigIntNullable(string $table, string $column, bool $nullable): void
    {
        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        $nullSql = $nullable ? 'NULL' : 'NOT NULL';

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` BIGINT {$nullSql}");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" TYPE BIGINT");
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" " . ($nullable ? 'DROP NOT NULL' : 'SET NOT NULL'));
            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement("ALTER TABLE [{$table}] ALTER COLUMN [{$column}] BIGINT {$nullSql}");
            return;
        }

        throw new \RuntimeException('change() fallback is disabled to avoid doctrine/dbal dependency in this migration.');
    }
};
