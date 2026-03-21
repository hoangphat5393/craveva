<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Set Seeding to true check if data is seeding.
        // This is required to stop notification in installation
        Config::set('app.seeding', true);

        Artisan::call('key:generate');

        $this->call(CountriesTableSeeder::class);
        $this->call(SmtpSettingsSeeder::class);
        $this->call(CoreDatabaseSeeder::class);
        // SAAS
        $this->call(CoreSuperAdminDatabaseSeeder::class);
        $this->call(ModulePermissionSeeder::class);

        $this->call(OrganisationSettingsTableSeeder::class);

        $this->call(PackageTableSeeder::class);
        $this->call(FrontSeeder::class);
        $this->call(GlobalCurrencyFormatSetting::class);

        // SAAS
        $this->call(SuperAdminRoleTableSeeder::class);

        Config::set('app.seeding', false);

        Cache::flush();
    }
}
