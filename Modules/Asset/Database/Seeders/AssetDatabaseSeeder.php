<?php

namespace Modules\Asset\Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Asset\Entities\Asset;
use Modules\Asset\Entities\AssetHistory;
use Modules\Asset\Entities\AssetType;

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
