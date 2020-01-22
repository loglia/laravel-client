<?php

namespace Loglia\LaravelClient;

use Illuminate\Log\LogManager;
use Illuminate\Support\ServiceProvider;
use Loglia\LaravelClient\Middleware\LogHttp;
use Loglia\LaravelClient\Monolog\LogliaFormatter;
use Loglia\LaravelClient\Monolog\LogliaMonologV1Handler;
use Loglia\LaravelClient\Monolog\LogliaMonologV2Handler;
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

        // TODO: singleton may not be needed
        $this->app->singleton(LogHttp::class);

        if ($this->app['log'] instanceof LogManager) {
            // In L5.6+, extend the log component with a loglia driver.
            $this->app['log']->extend('loglia', function ($app, array $config) {
                return LaravelClientServiceProvider::setUpLogger(new Logger('loglia'));
            });
        } else {
            // In older Laravel versions, modify Monolog to use the Loglia handler.
            LaravelClientServiceProvider::setUpLogger($this->app['log']->getMonolog());
        }
    }

    /**
     * Sets up and returns the provided Monolog logger.
     *
     * @param Logger $logger
     * @return Logger
     */
    public static function setUpLogger(Logger $logger)
    {
        $handler = new LogliaMonologV2Handler;

        if (Logger::API === 1) {
            // Laravel allows monolog v1 or v2, if the application is using v1 we must
            // use a handler compatible with monolog v1.
            $handler = new LogliaMonologV1Handler;
        }

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
    }
}
