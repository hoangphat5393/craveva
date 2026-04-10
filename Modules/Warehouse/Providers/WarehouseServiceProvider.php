<?php

namespace Modules\Warehouse\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Modules\Warehouse\Console\WarehouseReconciliationReportCommand;
use Modules\Warehouse\Services\InvoiceWarehouseStockService;
use Modules\Warehouse\Services\StockMovementService;
use Modules\Warehouse\Services\StockReservationService;
use Modules\Warehouse\Services\WarehouseAvailabilityService;
use Modules\Warehouse\Services\WarehouseFlowConfigService;
use Modules\Warehouse\Services\WarehouseFlowPolicyService;
use Modules\Warehouse\Services\WarehouseQueryService;
use Modules\Warehouse\Services\WarehouseReconciliationService;
use Modules\Warehouse\Services\WarehouseUnitConversionService;

class WarehouseServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Warehouse';

    protected string $moduleNameLower = 'warehouse';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        if (config('warehouse.inbound_from_purchase_order_delivered') && config('warehouse.inbound_from_delivery_order_received')) {
            Log::warning('Warehouse: WAREHOUSE_INBOUND_FROM_PO_DELIVERED and WAREHOUSE_INBOUND_FROM_DO_RECEIVED are both true — inbound may double-count; use only one canonical path in production.');
        }

        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->singleton(StockMovementService::class);
        $this->app->singleton(InvoiceWarehouseStockService::class);
        $this->app->singleton(StockReservationService::class);
        $this->app->singleton(WarehouseFlowConfigService::class);
        $this->app->singleton(WarehouseFlowPolicyService::class);
        $this->app->singleton(WarehouseAvailabilityService::class);
        $this->app->singleton(WarehouseUnitConversionService::class);
        $this->app->singleton(WarehouseReconciliationService::class);
        $this->app->singleton(WarehouseQueryService::class);
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $this->commands([
            WarehouseReconciliationReportCommand::class,
        ]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register translations.
     *
     * Always load defaults from the module, then merge published overrides from
     * resources/lang/modules/{warehouse} when that directory exists (Language Pack / deploy customisations).
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

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php')], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);

        $componentNamespace = str_replace('/', '\\', config('modules.namespace') . '\\' . $this->moduleName . '\\' . config('modules.paths.generator.component-class.path'));
        Blade::componentNamespace($componentNamespace, $this->moduleNameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

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
