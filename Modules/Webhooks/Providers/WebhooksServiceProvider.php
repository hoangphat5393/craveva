<?php

namespace Modules\Webhooks\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Modules\Webhooks\Console\ActivateModuleCommand;

class WebhooksServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    protected $moduleName = 'Webhooks';

    /**
     * @var string
     */
    protected $moduleNameLower = 'webhooks';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->commands(
            [
                ActivateModuleCommand::class,
            ]
        );

        $this->registerObservers();
    }

    /**
     * Register observers.
     *
     * @return void
     */
    public function registerObservers()
    {
        $webhookFor = \Modules\Webhooks\Entities\WebhooksSetting::WEBHOOK_FOR;

        // Manual mapping for models that don't match the simple name or are in Modules
        $modelMap = [
            'Client' => \App\Models\ClientDetails::class,
            'Employee' => \App\Models\EmployeeDetails::class,
            'CreditNotes' => \App\Models\CreditNotes::class,
            'PurchaseOrder' => \Modules\Purchase\Entities\PurchaseOrder::class,
            'PurchaseBill' => \Modules\Purchase\Entities\PurchaseBill::class,
            'PurchaseVendor' => \Modules\Purchase\Entities\PurchaseVendor::class,
            'PurchaseInventory' => \Modules\Purchase\Entities\PurchaseInventory::class,
            'RecruitJob' => \Modules\Recruit\Entities\RecruitJob::class,
            'RecruitJobApplication' => \Modules\Recruit\Entities\RecruitJobApplication::class,
            'ZoomMeeting' => \Modules\Zoom\Entities\ZoomMeeting::class,
            'Asset' => \Modules\Asset\Entities\Asset::class,
            'Warehouse' => \Modules\Warehouse\Entities\Warehouse::class,
            'Letter' => \Modules\Letter\Entities\Letter::class,
        ];

        foreach ($webhookFor as $name) {
            $modelClass = $modelMap[$name] ?? "App\\Models\\$name";

            // Check if model exists in App\Models or Modules
            if (!class_exists($modelClass)) {
                // Try Modules namespace (e.g. Modules\Asset\Entities\Asset)
                $moduleClass = "Modules\\$name\\Entities\\$name";
                if (class_exists($moduleClass)) {
                    $modelClass = $moduleClass;
                } else {
                    // Try fallback to the name itself if it happens to be a full class name
                    if (class_exists($name)) {
                        $modelClass = $name;
                    } else {
                        continue;
                    }
                }
            }

            // Final check: is it a Model?
            if (class_exists($modelClass) && is_subclass_of($modelClass, \Illuminate\Database\Eloquent\Model::class)) {
                // Check if specific observer exists
                // We check for exact match "NameObserver" or mapped observer if we knew it
                // But for Client, the observer is ClientDetailsObserver, which matches ClientDetails model basename.

                $basename = class_basename($modelClass);
                $observerClass = "Modules\\Webhooks\\Observers\\{$basename}Observer";

                if (class_exists($observerClass)) {
                    $modelClass::observe($observerClass);
                } else {
                    $modelClass::observe(\Modules\Webhooks\Observers\GenericObserver::class);
                }
            }
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');

        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );

        $this->mergeConfigFrom(
            module_path('webhooks', 'Config/webhooks.php'),
            'webhooks::webhooks'
        );
        $this->mergeConfigFrom(
            module_path('webhooks', 'Config/xss_ignore.php'),
            'webhooks::xss_ignore'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath,
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }

        return $paths;
    }
}
