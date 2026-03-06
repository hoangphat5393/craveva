<?php

use App\Models\ProjectTimeLog;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        ProjectTimeLog::whereNull('end_time')
            ->whereHas('tasksOnlyTrashed')
            ->update(['end_time' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
