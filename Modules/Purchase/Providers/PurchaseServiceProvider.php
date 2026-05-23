<?php

namespace Modules\Purchase\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Purchase\Console\ActivateModuleCommand;
use Modules\Purchase\Console\BackfillProductionFgInventoryLedgerCommand;
use Modules\Purchase\Console\GrnMigrateDataCommand;
use Modules\Purchase\Console\GrnMigrateRollbackCommand;
use Modules\Purchase\Console\SalesDoMigrateDataCommand;
use Modules\Purchase\Console\SalesDoMigrateRollbackCommand;
use Modules\Purchase\Console\SalesDoMigrationRehearsalCommand;
use Modules\Purchase\Console\SalesDoReconciliationReportCommand;
use Modules\Purchase\Console\VerifyCutoverSchemaCommand;
use Nwidart\Modules\Facades\Module;

class PurchaseServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

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
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->registerCommands();
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
            __DIR__ . '/../Config/config.php' => config_path('purchase.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php',
            'purchase'
        );

        $this->mergeConfigFrom(
            module_path('purchase', 'Config/xss_ignore.php'),
            'purchase::xss_ignore'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = base_path('resources/views/modules/purchase');

        $sourcePath = __DIR__ . '/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom([$sourcePath], 'purchase');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $moduleLangPath = __DIR__ . '/../Resources/lang';
        $publishedPath = base_path('resources/lang/modules/purchase');

        // LanguagePack source first; module/published paths override (see AppServiceProvider app merge).
        if (
            class_exists(Module::class)
            && Module::has('LanguagePack')
        ) {
            $languagePackPurchasePath = module_path('LanguagePack', 'Languages/modules/Purchase');
            if (is_dir($languagePackPurchasePath)) {
                $this->loadTranslationsFrom($languagePackPurchasePath, 'purchase');
            }
        }

        if (is_dir($moduleLangPath)) {
            $this->loadTranslationsFrom($moduleLangPath, 'purchase');
        }

        if (is_dir($publishedPath)) {
            $this->loadTranslationsFrom($publishedPath, 'purchase');
        }
    }

    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    public function registerFactories()
    {
        if (! app()->environment('production') && $this->app->runningInConsole()) {
            if (class_exists('Illuminate\Database\Eloquent\Factory')) {
                app()->make('Illuminate\Database\Eloquent\Factory')->load(__DIR__ . '/../Database/factories');
            }
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

    /**
     * Register artisan commands
     */
    private function registerCommands()
    {
        $this->commands(
            [
                ActivateModuleCommand::class,
                BackfillProductionFgInventoryLedgerCommand::class,
                SalesDoMigrationRehearsalCommand::class,
                SalesDoReconciliationReportCommand::class,
                SalesDoMigrateDataCommand::class,
                SalesDoMigrateRollbackCommand::class,
                GrnMigrateDataCommand::class,
                GrnMigrateRollbackCommand::class,
                VerifyCutoverSchemaCommand::class,
            ]
        );
    }
}
