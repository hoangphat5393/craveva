<?php

namespace Modules\ServerManager\Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Modules\ServerManager\Database\Seeders\ServerHostingSeeder;
use Modules\ServerManager\Database\Seeders\ServerDomainSeeder;

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
