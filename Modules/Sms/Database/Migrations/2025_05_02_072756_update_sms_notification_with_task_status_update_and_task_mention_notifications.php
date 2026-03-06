<?php

use App\Models\Company;
use Illuminate\Database\Migrations\Migration;
use Modules\Sms\Entities\SmsNotificationSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (SmsNotificationSetting::withoutGlobalScopes()->count()) {
            Company::get()->each(function ($company) {

                $setting = SmsNotificationSetting::withoutGlobalScope(CompanyScope::class)->firstOrNew([
                    'company_id' => $company->id,
                    'setting_name' => __('modules.emailNotification.task-status-changed'),
                    'slug' => 'task-status-changed',
                    'send_sms' => 'no',
                ]);
                $setting->saveQuietly();

                $setting = SmsNotificationSetting::withoutGlobalScope(CompanyScope::class)->firstOrNew([
                    'company_id' => $company->id,
                    'setting_name' => __('modules.emailNotification.task-mention'),
                    'slug' => 'task-mention-notification',
                    'send_sms' => 'no',
                ]);
                $setting->saveQuietly();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SmsNotificationSetting::where('slug', 'task-status-updated')->delete();
        SmsNotificationSetting::where('slug', 'task-mention-notification')->delete();
    }
};
