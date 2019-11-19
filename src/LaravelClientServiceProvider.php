<?php

namespace Loglia\LaravelClient;

use Illuminate\Support\ServiceProvider;
use Loglia\LaravelClient\Middleware\LogHttp;
use Loglia\LaravelClient\Monolog\LogliaFormatter;
use Loglia\LaravelClient\Monolog\LogliaHandler;
use Loglia\LaravelClient\Sticky\StickyContextProcessor;
use Monolog\Logger;

class LaravelClientServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/loglia.php' => config_path('loglia.php'),
        ], 'loglia');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/loglia.php', 'loglia');

        $this->app['log']->extend('loglia', function ($app, array $config) {
            $logger = new Logger('loglia');

            $handler = new LogliaHandler;

            if (config('loglia.api_key')) {
                $handler->setApiKey(config('loglia.api_key'));
            }

            if (config('loglia.endpoint')) {
                $handler->setEndpoint(config('loglia.endpoint'));
            }

            $handler->setFormatter(new LogliaFormatter(\DateTime::ISO8601));

            $handler->pushProcessor(new StickyContextProcessor);
            $logger->pushHandler($handler);

            return $logger;
        });

        $this->app->singleton(LogHttp::class);
    }
}
