<?php

use App\Models\TaskboardColumn;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        TaskboardColumn::where('slug', 'waiting-approval')->update([
            'slug' => 'waiting_approval',
        ]);
    }
};
