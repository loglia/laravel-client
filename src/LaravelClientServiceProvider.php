<?php

namespace Loglia\LaravelClient;

use Illuminate\Support\ServiceProvider;
use Loglia\LaravelClient\Middleware\LogHttp;

class LaravelClientServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/loglia.php' => config_path('loglia.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/loglia.php', 'loglia');

        $this->app->singleton(LogHttp::class);
    }
}
