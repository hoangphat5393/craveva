<?php

namespace Modules\Production\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Production\Services\ProductionFgQuantityPolicyService;
use Modules\Production\Services\ProductionOrderMaterialReservationService;
use Modules\Production\Services\ProductionPostingService;

class ProductionServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Production';

    protected string $moduleNameLower = 'production';

    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
    }

    public function register(): void
    {
        $this->app->singleton(ProductionOrderMaterialReservationService::class);
        $this->app->singleton(ProductionPostingService::class);
        $this->app->singleton(ProductionFgQuantityPolicyService::class);
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register translations.
     *
     * Load defaults bundled with this module first, then merge overrides from
     * resources/lang/modules/{production} when that directory exists (Language Pack publish / deployment).
     * Loading module defaults first avoids missing keys when a published folder is stale.
     */
    public function registerTranslations(): void
    {
        $moduleLangPath = module_path($this->moduleName, 'Resources/lang');

        $this->loadTranslationsFrom($moduleLangPath, $this->moduleNameLower);
        $this->loadJsonTranslationsFrom($moduleLangPath);

        $publishedPath = resource_path('lang/modules/' . $this->moduleNameLower);
        if (is_dir($publishedPath)) {
            $this->loadTranslationsFrom($publishedPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($publishedPath);
        }
    }

    protected function registerConfig(): void
    {
        $this->publishes([module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php')], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower);
    }

    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * @return array<int, string>
     */
    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }

        return $paths;
    }
}
