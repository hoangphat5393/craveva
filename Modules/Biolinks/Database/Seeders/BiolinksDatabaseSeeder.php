<?php

namespace Modules\Biolinks\Database\Seeders;

use Illuminate\Database\Seeder;

class BiolinksDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        config(['app.seeding' => true]);

        $faker = \Faker\Factory::create();

        config(['app.seeding' => false]);
    }
}
