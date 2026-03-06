<?php

namespace Modules\ServerManager\Database\Seeders;

use Illuminate\Database\Seeder;

class ServerManagerDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        config(['app.seeding' => true]);

        config(['app.seeding' => false]);
    }
}
