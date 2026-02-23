<?php

namespace Modules\Pricing\Providers;

use Illuminate\Support\ServiceProvider;

class PricingServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerTranslations();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerConfig()
    {
        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('pricing.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php',
            'pricing'
        );
    }

    protected function registerViews()
    {
        $viewPath = base_path('resources/views/modules/pricing');
        $sourcePath = __DIR__ . '/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom([$sourcePath], 'pricing');
    }

    protected function registerTranslations()
    {
        $langPath = base_path('resources/lang/modules/pricing');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'pricing');
        } else {
            $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'pricing');
        }
    }
}
