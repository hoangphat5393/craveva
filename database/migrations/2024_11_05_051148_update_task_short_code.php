<?php

use App\Models\Task;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        $tasks = Task::whereNull('task_short_code')->whereNotNull('project_id')->get();

        foreach ($tasks as $task) {

            try {
                $project = $task->project;
                $task->task_short_code = $project->project_short_code.'-'.$task->id;
                $task->saveQuietly();
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
