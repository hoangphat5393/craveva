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
        $tableGateway = 'global_payment_gateway_credentials';
        $this->renameColumnSafely($tableGateway, 'razorpay_webhook_secret', 'live_razorpay_webhook_secret');

        if (! Schema::hasColumn($tableGateway, 'test_razorpay_webhook_secret')) {
            Schema::table($tableGateway, function (Blueprint $table) {
                $table->string('test_razorpay_webhook_secret')->nullable()->after('live_razorpay_webhook_secret');
            });
        }

        if (! Schema::hasColumn($tableGateway, 'live_razorpay_webhook_secret')) {
            Schema::table($tableGateway, function (Blueprint $table) {
                $table->string('live_razorpay_webhook_secret')->nullable()->after('test_razorpay_webhook_secret');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
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
};
