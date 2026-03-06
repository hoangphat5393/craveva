<?php

namespace Database\Seeders;

use App\Console\Commands\FixUpgradeCompanyCommand;
use App\Models\LanguageSetting;
use App\Models\SuperAdmin\FrontDetail;
use Illuminate\Database\Seeder;

class FrontSeeder extends Seeder
{
    public function run()
    {
        $language = LanguageSetting::where('language_code', 'en')->first();

        // Manually call observer
        event('eloquent.saved: App\Models\LanguageSetting', $language);

        $this->frontDetailsInsert();

        $command = new FixUpgradeCompanyCommand;
        $command->languageFixFront();
    }

    private function frontDetailsInsert()
    {
        $frontDetails = new FrontDetail;
        $frontDetails->primary_color = '#453130';
        $frontDetails->get_started_show = 'yes';
        $frontDetails->sign_in_show = 'yes';
        $frontDetails->address = '4868  Ben Street Lansing Michigan 48906';
        $frontDetails->phone = '+91 1234567890';
        $frontDetails->email = 'company@example.com';
        $frontDetails->locale = 'en';

        $frontDetails->social_links = json_encode([
            ['name' => 'facebook', 'link' => 'https://www.facebook.com/cravevaapp'],
            ['name' => 'twitter', 'link' => 'https://twitter.com/cravevaapp'],
            ['name' => 'instagram', 'link' => 'https://www.instagram.com/cravevaapp/'],
            ['name' => 'dribbble', 'link' => 'https://dribbble.com'],
        ]);

        $frontDetails->save();
    }
}
