<?php

namespace Modules\ServerManager\Listeners;

use App\Events\NewCompanyCreatedEvent;
use Modules\ServerManager\Database\Seeders\ServerProviderSeeder;
use Modules\ServerManager\Database\Seeders\ServerTypeSeeder;
use Modules\ServerManager\Entities\ServerSetting;

class CompanyCreatedListener
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(NewCompanyCreatedEvent $event)
    {
        $company = $event->company;

        // Initialize ServerManager module settings for the new company
        ServerSetting::addModuleSetting($company);

        $this->seedInitialData($company);
    }

    /**
     * Seed initial data for the module
     */
    public static function seedInitialData($company)
    {
        // Seed server providers
        $providerSeeder = new ServerProviderSeeder;
        $providerSeeder->seedProvidersForCompany($company->id);

        // Seed server types
        $serverTypeSeeder = new ServerTypeSeeder;
        $serverTypeSeeder->seedServerTypesForCompany($company->id);
    }
}
