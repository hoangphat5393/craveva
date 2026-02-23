<?php

namespace Modules\Biolinks\Database\Seeders;

use App\Models\Company;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Modules\Biolinks\Entities\Biolink;
use Modules\Biolinks\Entities\BiolinkBlocks;
use Modules\Biolinks\Entities\BiolinkSetting;
use Modules\Biolinks\Enums\Font;
use Modules\Biolinks\Enums\Heading;
use Modules\Biolinks\Enums\PaypalType;

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
