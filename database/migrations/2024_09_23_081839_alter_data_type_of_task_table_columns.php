<?php

use App\Models\Task;
use Carbon\Carbon;
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
        $this->setDateTimeNullable('tasks', 'start_date', true);
        $this->setDateTimeNullable('tasks', 'due_date', true);

        Task::whereNotNull('created_at')
            ->whereRaw("TIME(start_date) = '00:00:00' AND TIME(due_date) = '00:00:00'")
            ->update([
                'start_date' => DB::raw("CONCAT(DATE(start_date), ' ', TIME(created_at))"),
                'due_date' => DB::raw("CONCAT(DATE(due_date), ' ', TIME(created_at))"),
            ]);

        //        $tasks = Task::whereNotNull('created_at')
        //            ->whereRaw("TIME(start_date) = '00:00:00' AND TIME(due_date) = '00:00:00'")
        //            ->get();
        //
        //        $tasks->each(function($row) {
        //            $startDate = Carbon::parse($row->start_date)->format('Y-m-d');
        //            $dueDate = Carbon::parse($row->due_date)->format('Y-m-d');
        //            $createdAtTime = Carbon::parse($row->created_at)->format('H:i:s');
        //
        //            $newStartDate = Carbon::parse("{$startDate} {$createdAtTime}");
        //            $newDueDate = Carbon::parse("{$dueDate} {$createdAtTime}");
        //
        //            // Perform bulk update in one query for each task
        //            $row->update([
        //                'start_date' => $newStartDate,
        //                'due_date' => $newDueDate,
        //            ]);
        //        });

        //        Task::whereNotNull('created_at')->get()->each(function($row) {
        //
        //            if (Carbon::parse($row->start_date)->format('H:i:s') === '00:00:00' && Carbon::parse($row->start_date)->format('H:i:s') === '00:00:00') {
        //
        //                $startDate = Carbon::parse($row->start_date)->format('Y-m-d');
        //                $dueDate = Carbon::parse($row->due_date)->format('Y-m-d');
        //
        //                $createdAtTime = Carbon::parse($row->created_at)->format('H:i:s');
        //
        //                $newStartDate = Carbon::parse("{$startDate} {$createdAtTime}");
        //                $newDueDate = Carbon::parse("{$dueDate} {$createdAtTime}");
        //
        //                Task::where('id', $row->id)->update([
        //                        'start_date' => $newStartDate,
        //                        'due_date' => $newDueDate,
        //                    ]);
        //            }
        //        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->setDate('tasks', 'start_date', true);
        $this->setDate('tasks', 'due_date', true);
    }

    private function setDateTimeNullable(string $table, string $column, bool $nullable): void
    {
        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        $nullSql = $nullable ? 'NULL' : 'NOT NULL';

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` DATETIME {$nullSql}");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" TYPE TIMESTAMP");
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" " . ($nullable ? 'DROP NOT NULL' : 'SET NOT NULL'));
            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement("ALTER TABLE [{$table}] ALTER COLUMN [{$column}] DATETIME2 {$nullSql}");
            return;
        }

        throw new \RuntimeException('change() fallback is disabled to avoid doctrine/dbal dependency in this migration.');
    }

    private function setDate(string $table, string $column, bool $nullable): void
    {
        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        $nullSql = $nullable ? 'NULL' : 'NOT NULL';

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` DATE {$nullSql}");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" TYPE DATE");
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" " . ($nullable ? 'DROP NOT NULL' : 'SET NOT NULL'));
            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement("ALTER TABLE [{$table}] ALTER COLUMN [{$column}] DATE {$nullSql}");
            return;
        }

        throw new \RuntimeException('change() fallback is disabled to avoid doctrine/dbal dependency in this migration.');
    }
};
