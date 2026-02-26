<?php

namespace Modules\FuncNews\Providers;

use Illuminate\Support\ServiceProvider;

class FuncNewsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'funcnews');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(module_path('FuncNews', 'Resources/views'), 'funcnews');
    }
}
