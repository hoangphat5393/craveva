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
        ModuleSetting::where('type', 'client')->where('module_name', 'clients')->delete();
    }
};
