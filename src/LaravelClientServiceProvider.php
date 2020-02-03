<?php

namespace Loglia\LaravelClient;

use Monolog\Logger;
use Ramsey\Uuid\Uuid;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Loglia\LaravelClient\Exceptions\LogliaException;
use Loglia\LaravelClient\Middleware\LogHttp;
use Illuminate\Database\Events\QueryExecuted;
use Loglia\LaravelClient\Sticky\StickyContext;
use Loglia\LaravelClient\Monolog\LogliaHandler;
use Loglia\LaravelClient\Monolog\LogliaTransport;
use Loglia\LaravelClient\Monolog\LogliaFormatter;
use Loglia\LaravelClient\Sticky\StickyContextProcessor;

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

        // Add a trace UUID which can be used to correlate a collection of logs together.
        StickyContext::add('__loglia', [
            'trace_uuid' => Uuid::uuid4()->toString()
        ]);

        DB::listen(function (QueryExecuted $query) {
            // Checked inside ::listen to allow the user to toggle SQL logging on and off
            // by changing config at runtime.
            if (! config('loglia.sql.enabled', true)) {
                return;
            }

            $classesToRemoveFromTrace = [
                LaravelClientServiceProvider::class,
                \Illuminate\Events\Dispatcher::class,
                \Illuminate\Database\Connection::class,
                \Illuminate\Database\Query\Builder::class,
                \Illuminate\Database\Eloquent\Builder::class,
                \Illuminate\Database\Concerns\BuildsQueries::class
            ];

            $fullTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $relevantTrace = $fullTrace;

            $counter = 0;
            foreach ($fullTrace as $frame) {
                if (in_array($frame['class'], $classesToRemoveFromTrace)) {
                    $counter++;
                    continue;
                }

                $relevantTrace = array_slice($fullTrace, $counter - 1);
            }

            $context = [
                '__loglia' => [
                    'type' => 'sql',
                    'query' => $query->sql,
                    'connection' => $query->connectionName,
                    'time' => $query->time,
//                    'trace' => $relevantTrace
                ]
            ];

            if (config('loglia.sql.log_bindings', true)) {
                $context['__loglia']['bindings'] = $query->bindings;
            }

            Log::info('Executed SQL query', $context);
        });
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

        $handler = new LogliaHandler($transport);
        $handler->setFormatter(new LogliaFormatter(\DateTime::ISO8601));
        $handler->pushProcessor(new StickyContextProcessor);

        $logger->pushHandler($handler);

        return $logger;
    }
}
