<?php

use App\Models\UserAuth;
use App\Scopes\ActiveScope;
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
        if (! isCraveva()) {
            return true;
        }

        $this->setDoubleNullable('global_invoices', 'sub_total', true);
        $this->setDoubleNullable('global_invoices', 'total', true);

        Schema::table('front_details', function (Blueprint $table) {
            $table->enum('homepage_background', ['default', 'color', 'image', 'image_and_color'])->default('default');
            $table->string('background_color')->nullable()->default('#CDDCDC');
            $table->string('background_image')->nullable();
        });

        $this->setDoubleNullable('packages', 'annual_price', true);
        $this->setDoubleNullable('packages', 'monthly_price', true);

        // Delete the users that are not in users table
        UserAuth::withoutGlobalScope(ActiveScope::class)
            ->doesntHave('users')
            ->delete();
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
