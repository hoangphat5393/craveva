<?php

use App\Models\Module;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add ServerManager module to modules table
        Module::firstOrCreate([
            'module_name' => 'servermanager',
        ], [
            'description' => 'Manage server hostings, domains, and related services',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Module::where('module_name', 'servermanager')->delete();
    }
};
