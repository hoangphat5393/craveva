<?php

use App\Models\ModuleSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        ModuleSetting::where('module_name', 'settings')->update([
            'is_allowed' => 1,
        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
