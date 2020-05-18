<?php

namespace Loglia\LaravelClient;

use Illuminate\Log\LogManager;
use Illuminate\Support\ServiceProvider;
use Loglia\LaravelClient\Exceptions\LogliaException;
use Loglia\LaravelClient\Middleware\LogHttp;
use Loglia\LaravelClient\Monolog\LogliaFormatter;
use Loglia\LaravelClient\Monolog\LogliaHandler;
use Loglia\LaravelClient\Monolog\LogliaTransport;
use Loglia\LaravelClient\Sticky\StickyContextProcessor;
use Monolog\Handler\BufferHandler;
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
     * @throws LogliaException
     * @return Logger
     */
    public static function setUpLogger(Logger $logger)
    {
        $transport = new LogliaTransport(config('loglia.api_key'));

        $logliaHandler = new LogliaHandler($transport);
        $logliaHandler->setFormatter(new LogliaFormatter(\DateTime::ISO8601));
        $logliaHandler->pushProcessor(new StickyContextProcessor);

        $logger->pushHandler(new BufferHandler(
            $logliaHandler,
            50,
            Logger::DEBUG,
            true,
            true
        ));

        return $logger;
    }
}
