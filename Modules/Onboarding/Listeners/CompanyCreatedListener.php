<?php

namespace Modules\Onboarding\Listeners;

use App\Models\DashboardWidget;
use Modules\Onboarding\Entities\OnboardingNotificationSetting;
use Modules\Onboarding\Entities\OnboardingSetting;
use Modules\Onboarding\Entities\OnboardingTask;

class CompanyCreatedListener
{
    public function handle($event)
    {
        $company = $event->company;
        OnboardingSetting::addModuleSetting($company);
        OnboardingTask::createDefaultTasks($company);
        OnboardingNotificationSetting::addNotificationSetting($company);

        $this->createWidget($company);
    }

    public function createWidget($company)
    {
        $existingWidget = DashboardWidget::where('company_id', $company->id)
            ->where('widget_name', 'onboarding')->where('dashboard_type', 'private-dashboard')
            ->first();

        if (! $existingWidget) {
            $widget = [
                [
                    'widget_name' => 'onboarding',
                    'status' => 1,
                    'company_id' => $company->id,
                    'dashboard_type' => 'private-dashboard',
                ],
            ];

            DashboardWidget::insert($widget);
        }

        $existingWidget = DashboardWidget::where('company_id', $company->id)
            ->where('widget_name', 'onboarding')->where('dashboard_type', 'admin-hr-dashboard')
            ->first();

        if (! $existingWidget) {
            $widget = [
                [
                    'widget_name' => 'onboarding',
                    'status' => 1,
                    'company_id' => $company->id,
                    'dashboard_type' => 'admin-hr-dashboard',
                ],
            ];

            DashboardWidget::insert($widget);
        }
    }
}
