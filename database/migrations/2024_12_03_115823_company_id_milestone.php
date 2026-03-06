<?php

use App\Models\ProjectMilestone;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $milestones = ProjectMilestone::with('project')->whereNull('company_id')->get();

        foreach ($milestones as $milestone) {
            $milestone->company_id = $milestone->project->company_id;
            $milestone->saveQuietly();
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
