<?php

use App\Helper\Files;
use App\Models\Task;
use App\Models\TaskFile;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all tasks that are deleted
        $deletedTasks = Task::onlyTrashed()->get();

        if ($deletedTasks) {
            foreach ($deletedTasks as $deletedTask) {

                $taskFiles = TaskFile::where('task_id', $deletedTask->id)->get();

                foreach ($taskFiles as $file) {
                    // Construct file path and delete the file from storage
                    $filePath = TaskFile::FILE_PATH.'/'.$file->task_id;

                    Files::deleteFile($file->hashname, TaskFile::FILE_PATH);
                    Files::deleteDirectory($filePath);
                    $file->delete();
                }

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
