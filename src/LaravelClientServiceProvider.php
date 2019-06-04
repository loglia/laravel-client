<?php

namespace Retrospekt\LaravelClient;

use Illuminate\Support\ServiceProvider;
use Retrospekt\LaravelClient\Middleware\LogHttp;

class LaravelClientServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/retrospekt.php.php' => config_path('retrospekt.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/retrospekt.php', 'retrospekt');

        $this->app->singleton(LogHttp::class);
    }
}
