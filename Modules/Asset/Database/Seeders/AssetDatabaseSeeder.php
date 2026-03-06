<?php

namespace Modules\Asset\Database\Seeders;

use Illuminate\Database\Seeder;

class AssetDatabaseSeeder extends Seeder
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
