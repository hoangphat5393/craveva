<?php

namespace App\Providers;

use App\Models\Company;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Cashier 15+ / Sanctum 4+: migrations are opt-in via `php artisan vendor:publish` (no ignoreMigrations API).

        if (config('app.redirect_https')) {
            $this->app['request']->server->set('HTTPS', true);
        }

        if (app()->environment(['development', 'local', 'craveva'])) {
            $ideHelperProvider = \Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class;

            if (class_exists($ideHelperProvider)) {
                $this->app->register($ideHelperProvider);
            }
        }
    }

    public function boot()
    {
        Cashier::useCustomerModel(Company::class);

        if (config('app.redirect_https')) {
            URL::forceScheme('https');
        }

        Schema::defaultStringLength(191);

        CarbonInterval::macro('formatHuman', function ($totalMinutes, $seconds = false): string {

            if ($seconds) {
                return CarbonInterval::seconds($totalMinutes)->cascade()->forHumans(['short' => true, 'options' => 0]);
                /** @phpstan-ignore-line */
            }

            return CarbonInterval::minutes($totalMinutes)->cascade()->forHumans(['short' => true, 'options' => 0]);
            /** @phpstan-ignore-line */
        });

        //    Model::preventLazyLoading(app()->environment('development'));

    }
}
